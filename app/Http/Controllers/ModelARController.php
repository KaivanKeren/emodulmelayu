<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModelAR;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ModelARController extends Controller
{
    public function apiIndex()
    {
        $models = ModelAR::latest()->get();
        return response()->json(['data' => $models], 200);
    }

    public function apiStore(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'asset' => 'required|mimes:glb|max:20480',
            ]);

            // Penyimpanan file
            $file = $request->file('asset');
            $path = $file->store('models/glb', 'public');

            // Simpan data ke database
            ModelAR::create([
                'title' => $request->title,
                'description' => $request->description,
                'asset' => $path,
            ]);

            return redirect()->route('models.index')->with('success', 'Model created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage())->withInput();
        }
    }


    public function apiShow(ModelAR $model)
    {
        return response()->json(['data' => $model], 200);
    }

    public function apiDestroy(ModelAR $model)
    {
        Storage::disk('public')->delete($model->asset);
        $model->delete();
        return response()->json(['message' => 'Model deleted successfully'], 200);
    }
}
