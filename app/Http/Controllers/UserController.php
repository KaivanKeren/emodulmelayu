<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::all();

        // Get sort and direction from request if provided
        $sort = $request->input('sort', 'name'); // Changed default to name
        $direction = $request->input('direction', 'asc');

        // Validate sort parameter to prevent SQL injection
        $allowedSorts = ['name', 'email', 'created_at', 'status', 'role'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name'; // Changed default to name
        }

        // Validate direction parameter
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $users = User::with('school')
            ->orderBy($sort, $direction)
            ->paginate(10);

        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.users.index', compact('users', 'pendingUsers', 'schools'));
    }

    public function showPassword($id)
    {
        $user = User::findOrFail($id);

        try {
            $decryptedPassword = Crypt::decryptString($user->first_password);
            return response()->json([
                'password' => $decryptedPassword,
                'status' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mendekripsi password',
                'status' => false
            ]);
        }
    }

    public function filter(Request $request)
    {
        // Retrieve filters from request
        $filters = $request->only(['name', 'role', 'school', 'status']);

        // Build query with optional filters
        $query = User::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['school'])) {
            $query->whereHas('school', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['school'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Get users with pagination
        $users = $query->paginate(10);
        $pendingUsers = User::where('status', 'Pending')->count();
        $schools = School::all();

        // Return view with users and filters
        return view('admin.users.index', compact('users', 'filters', 'schools', 'pendingUsers'));
    }

    // public function create()
    // {
    //     return view('admin.users.create');
    // }

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
        $schools = School::all();
        $pendingUsers = User::where('status', 'Pending')->count();
        return view('admin.users.edit', compact('user', 'schools', 'pendingUsers'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'nisn_nip' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) {
                    $length = strlen($value);
                    if ($length !== 10 && $length !== 18) {
                        $fail('NISN/NIP harus 10 digit (untuk siswa) atau 18 digit (untuk guru).');
                    }
                },
            ],
            'role' => 'sometimes|in:Admin,Guru,Siswa',
            'school_id' => 'sometimes|exists:schools,id', // Pastikan ID sekolah valid
            'status' => 'sometimes|boolean',
            'password' => 'nullable|min:6', // Password opsional
        ]);

        // Handle password
        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        } else {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        // Update user
        $user->update($validatedData);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function accept($id)
    {
        $user = User::findOrFail($id);
        if ($user->status === 'Pending') {
            $user->status = 'Accepted';
            $user->save();

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diterima.');
        }

        return redirect()->route('users.index')->with('error', 'Aksi tidak valid.');
    }

    public function acceptAll()
    {
        User::where('status', 'Pending')->update(['status' => 'Accepted']);
        return redirect()->back()->with('success', 'Semua pengguna pending telah diterima');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    // API Method
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
            'school_id' => 'required',
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
            'school_id' => 'sometimes',
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
