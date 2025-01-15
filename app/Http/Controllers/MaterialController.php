<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\ModelAR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $models = ModelAR::all();
        return view('admin.material.create', compact('models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'asset' => 'required|mimes:pdf|max:50000',
            'model_id' => 'required|exists:model_a_r_s,id'
        ]);

        $file = $request->file('asset');
        $path = $file->store('materials/pdf', 'public');

        Material::create([
            'title' => $request->title,
            'description' => $request->description,
            'asset' => $path,
            'user_id' => auth()->id(),
            'model_id' => $request->model_id
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
        $models = ModelAR::all();

        return view('admin.material.edit', compact('material', 'models'));
    }

    public function update(Request $request, Material $material)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'model_id' => 'required|exists:model_a_r_s,id',
            'asset' => 'nullable|mimes:pdf|max:10240', // Max 10MB
        ]);

        try {
            // Start transaction
            DB::beginTransaction();

            // Handle file upload if new file is provided
            if ($request->hasFile('asset')) {
                // Delete old file
                if ($material->asset && Storage::disk('public')->exists($material->asset)) {
                    Storage::disk('public')->delete($material->asset);
                }

                // Store new file
                $filePath = $request->file('asset')->store('materials', 'public');
                $validated['asset'] = $filePath;
            }

            // Update material
            $material->update($validated);

            // Commit transaction
            DB::commit();

            return redirect()
                ->route('materials.index')
                ->with('success', 'Material berhasil diperbarui');

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui material. Silakan coba lagi.');
        }
    }


    public function destroy(Material $material)
    {
        Storage::disk('public')->delete($material->asset);
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Material deleted successfully');
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
