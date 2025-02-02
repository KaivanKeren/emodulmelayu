<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Models\ModelAR;
use App\Models\User;
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
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.material.index', compact('materials', 'pendingUsers'));
    }

    public function filter(Request $request)
    {
        // Retrieve filters from request
        $filters = $request->only(['title', 'user', 'description']);

        // Build query with optional filters
        $query = Material::query();

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['user'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['user'] . '%');
            });
        }

        if (!empty($filters['description'])) {
            $query->where('description', $filters['description']);
        }

        // Get users with pagination
        $materials = $query->paginate(10);

        // Return view with materials and filters
        return view('admin.material.index', compact('materials', 'filters'));
    }

    public function create()
    {
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.material.create', compact('pendingUsers'));
    }

    public function store(MaterialRequest $request)
    {
        try {
            $material = Material::create([
                'title' => $request->title,
                'description' => $request->description,
                'assets' => $this->processUrls($request->drive_urls),
                'user_id' => auth()->id(),
            ]);

            return $this->sendResponse($material, 'Material created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating material: ' . $e->getMessage());
            return $this->sendError('Error creating material', 500);
        }
    }

    /**
     * Process array of Google Drive URLs
     *
     * @param array|null $urls
     * @return string
     */
    private function processUrls(?array $urls): string
    {
        if (empty($urls)) {
            return json_encode([]);
        }

        $processedUrls = array_map(function ($url) {
            return $this->standardizeUrl($url);
        }, array_filter($urls));

        return json_encode(array_values($processedUrls));
    }

    /**
     * Standardize Google Drive URL format
     *
     * @param string|null $url
     * @return string|null
     */
    private function standardizeUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Ensure URL starts with https://
        $url = preg_replace('/^(?!https:\/\/)/', 'https://', $url);

        // Extract file ID and standardize format
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/view";
        }

        // Remove any trailing parameters and ensure /view at the end
        return preg_replace('/\/[a-z]+\?.*$|\/[a-z]+$/', '/view', $url);
    }

    /**
     * Send success response
     *
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function sendResponse($data, string $message)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'material' => $data
            ]);
        }

        return redirect()->route('materials.index')
            ->with('success', $message);
    }

    /**
     * Send error response
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function sendError(string $message, int $code = 400)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], $code);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    public function show($id)
    {
        $material = Material::findOrFail($id);
        $pendingUsers = User::where('status', 'Pending')->count();

        return view('admin.material.show', compact('material', 'pendingUsers'));
    }

    public function edit(Material $material)
    {
        // $material->assets = json_decode($material->assets);
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.material.edit', compact('material', 'pendingUsers'));
    }

    public function update(MaterialRequest $request, Material $material)
    {
        try {
            $material->update([
                'title' => $request->title,
                'description' => $request->description,
                'assets' => $this->processUrls($request->drive_urls),
            ]);

            return $this->sendResponse($material, 'Material updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating material: ' . $e->getMessage());
            return $this->sendError('Error updating material', 500);
        }
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
        $materials = Material::with(['user'])->latest()->get();

        $formattedMaterials = $materials->map(function ($material) {
            return [
                'id' => $material->id,
                'name' => $material->title,
                'description' => $material->description,
                'author' => $material->user ? $material->user->name : 'Unknown',
            ];
        });

        return response()->json(['data' => $formattedMaterials], 200);
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
        $material->load(['user', 'model']);

        // Ubah format response untuk hanya menampilkan nama file dari assets
        $formattedMaterial = [
            'id' => $material->id,
            'filename' => collect(json_decode($material->assets))->map(function ($asset) {
                return basename($asset); // Mengambil hanya nama file
            }),
        ];

        return response()->json(['data' => $formattedMaterial], 200);
    }


    public function apiDestroy(Material $material)
    {
        Storage::disk('public')->delete($material->asset);
        $material->delete();
        return response()->json(['message' => 'Material deleted successfully'], 200);
    }
}
