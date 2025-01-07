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
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'email' => 'required',
                'nisn_nip' => 'required',
                'role' => 'required',
                'school' => 'required',
                'status' => 'required',
                'password' => 'required',
            ]
        );

        User::create($request->all());

        return redirect()->route('admin.users.index')
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

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    public function accept($id)
    {
        $user = User::findOrFail($id);
        if ($user->status === 'pending') {
            $user->status = 'accepted';
            $user->save();

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diterima.');
        }

        return redirect()->route('users.index')->with('error', 'Aksi tidak valid.');
    }


    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function apiIndex()
    {
        $users = User::paginate(10);
        return response()->json([
            'code' => 200,
            'message' => 'Show all users successfully',
            'data' => $users
        ]);
    }

    public function apiShow(User $user)
    {
        return response()->json([
            'code' => 200,
            'message' => 'User details retrieved successfully',
            'data' => $user
        ]);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'nisn_nip' => 'required',
            'role' => 'required',
            'school' => 'required',
            'password' => 'required|min:6',
        ]);

        $user = User::create($request->all());

        return response()->json([
            'code' => 201,
            'message' => 'User created successfully.',
            'data' => $user
        ], 201);
    }

    public function apiUpdate(Request $request, User $user)
    {
        $validation = [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'nisn_nip' => 'sometimes',
            'role' => 'sometimes',
            'school' => 'sometimes',
            'status' => 'sometimes',
            'password' => 'sometimes|min:6',
        ];

        $request->validate($validation);

        $data = $request->only(['name', 'email', 'nisn_nip', 'role', 'school', 'status']);

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return response()->json([
            'code' => 200,
            'message' => 'User updated successfully.',
            'data' => $user
        ]);
    }

    public function apiDestroy(User $user)
    {
        $user->delete();

        return response()->json([
            'code' => 200,
            'message' => 'User deleted successfully.'
        ]);
    }
}
