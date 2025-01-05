<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function dashboard()
    {
        $users = User::all();
        $total_users = User::count();

        $materials = Material::count();
        $assessments = Assessment::count();
        $discussions = Discussion::count();
        return view('dashboard', compact('users', 'total_users', 'materials', 'assessments', 'discussions'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'nisn_nip' => 'required',
            'role' => 'required',
            'school' => 'required',
            'status' => 'required',
            'password' => 'required',
        ]);

        User::create($request->all());

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'nisn_nip' => 'required',
            'role' => 'required',
            'school' => 'required',
            'status' => 'required',
            'password' => 'required',
        ]);

        $user->update($request->all());

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
