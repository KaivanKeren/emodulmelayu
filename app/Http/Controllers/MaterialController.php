<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\ModelAR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with(['user', 'model'])->latest()->paginate(10);
        return view('admin.material.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.material.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assets.*' => 'required|file|mimes:pdf,mp4,mov,avi|max:102400', // 100MB max file 
        ]);

        $paths = [];

        if ($request->hasFile('assets')) {
            foreach ($request->file('assets') as $file) {
                // Get file extension
                $extension = $file->getClientOriginalExtension();

                // Determine storage directory based on file type
                $directory = in_array($extension, ['mp4', 'mov', 'avi'])
                    ? 'materials/videos'
                    : 'materials/pdf';

                // Store file and get path
                $paths[] = $file->store($directory, 'public');
            }
        }

        Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'assets' => json_encode($paths), // Store paths as JSON string
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('materials.index')->with('success', 'Material created successfully');
    }

    public function show($id)
    {
        $material = Material::findOrFail($id);
        return view('admin.material.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $material->assets = json_decode($material->assets, true);
        return view('admin.material.edit', compact('material'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assets' => 'nullable|array', // Tidak wajib meng-upload file baru
            'assets.*' => 'file|mimes:pdf,mp4,mov,avi|max:102400',
        ]);

        $material = Material::findOrFail($id);
        $material->title = $request->title;
        $material->description = $request->description;

        // Jika ada file baru, hapus file lama dan simpan yang baru
        if ($request->hasFile('assets')) {
            // Hapus file lama yang ada
            foreach (json_decode($material->assets) as $asset) {
                Storage::delete('public/' . $asset); // Hapus file lama
            }

            // Upload file baru
            $newAssets = [];
            foreach ($request->file('assets') as $file) {
                $path = $file->store('materials', 'public'); // Menyimpan file baru di folder public/materials
                $newAssets[] = $path;
            }
            $material->assets = json_encode($newAssets); // Menyimpan array ke dalam database
        }

        $material->save();

        return redirect()->route('materials.index')->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        Storage::disk('public')->delete($material->assets);
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Material deleted successfully');
    }

    public function deleteAsset(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Menghapus file dari storage
        if (Storage::exists($path)) {
            Storage::delete($path);

            // Menghapus file dari database (misalnya pada model Material)
            $material = Material::find($request->input('material_id')); // Ambil material berdasarkan ID
            $assets = json_decode($material->assets, true); // Mengambil assets yang berupa array

            // Menghapus path file yang ingin dihapus
            if (($key = array_search($path, $assets)) !== false) {
                unset($assets[$key]); // Hapus file dari array
                $material->assets = json_encode(array_values($assets)); // Simpan kembali
                $material->save(); // Simpan perubahan ke database
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File tidak ditemukan.']);
    }



    // API Method
    public function apiIndex()
    {
        $materials = Material::with(['user', 'model'])->latest()->get();
        return response()->json(['data' => $materials], 200);
    }

    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'asset' => 'required|mimes:pdf|max:10240', // 10MB max
            'model_id' => 'required|exists:models,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('asset');
        $path = $file->store('materials/pdf', 'public');

        $material = Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'asset' => $path,
            'user_id' => auth()->id(),
            'model_id' => $request->model_id
        ]);

        return response()->json(['data' => $material, 'message' => 'Material created successfully'], 201);
    }

    public function apiShow(Material $material)
    {
        return response()->json(['data' => $material->load(['user', 'model'])], 200);
    }

    public function apiDestroy(Material $material)
    {
        Storage::disk('public')->delete($material->asset);
        $material->delete();
        return response()->json(['message' => 'Material deleted successfully'], 200);
    }
}
