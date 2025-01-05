<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModelAR;

class ModelARController extends Controller
{
    public function index()
    {
        return view('modelar.index');
    }

    public function create()
    {
        return view('modelar.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'asset' => 'required'
        ]);

        ModelAR::create($validatedData);

        return redirect()->route('modelar.index')->with('success', 'ModelAR created successfully.');
    }

    public function show($id)
    {
        $modelAR = ModelAR::findOrFail($id);
        return view('modelar.show', compact('modelAR'));
    }

    public function edit($id)
    {
        $modelAR = ModelAR::findOrFail($id);
        return view('modelar.edit', compact('modelAR'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'asset' => 'required'
        ]);

        ModelAR::whereId($id)->update($validatedData);

        return redirect()->route('modelar.index')->with('success', 'ModelAR updated successfully.');
    }

    public function destroy($id)
    {
        $modelAR = ModelAR::findOrFail($id);
        $modelAR->delete();

        return redirect()->route('modelar.index')->with('success', 'ModelAR deleted successfully.');
    }
}
