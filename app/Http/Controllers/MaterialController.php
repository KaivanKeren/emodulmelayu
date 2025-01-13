<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with('user', 'model')->paginate(10);
        return view('admin.material.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.material.create');
    }
}
