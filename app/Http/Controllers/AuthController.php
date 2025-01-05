<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Web methods
    public function login()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Kredensial tidak valid.');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function postRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'school' => 'required',
            'nisn_nip' => [
                'required',
                'string',
                'unique:users',
                function ($attribute, $value, $fail) {
                    $length = strlen($value);
                    if ($length !== 10 && $length !== 18) {
                        $fail('NISN/NIP harus 10 digit (untuk siswa) atau 18 digit (untuk guru).');
                    }
                },
            ],
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'school.required' => 'Sekolah wajib diisi.',
            'nisn_nip.required' => 'NISN/NIP wajib diisi.',
            'nisn_nip.unique' => 'NISN/NIP sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Determine role based on NISN/NIP length
        $role = strlen($request->nisn_nip) === 10 ? 'siswa' : 'guru';

        // Create user with role
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn_nip' => $request->nisn_nip,
            'school' => $request->school,
            'password' => bcrypt($request->password),
            'role' => $role,
        ]);

        return redirect()->route('login')
            ->with('success', 'User berhasil dibuat.');
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('login');
    }

    // API methods
    public function apiLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Kredensial tidak valid.'
        ], 401);
    }

    public function apiRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'school' => 'required',
            'nisn_nip' => 'required|string|unique:users',
            'password' => 'required|min:8'
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'school.required' => 'Sekolah wajib diisi.',
            'nisn_nip.required' => 'NISN/NIP wajib diisi.',
            'nisn_nip.unique' => 'NISN/NIP sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'school' => $validated['school'],
            'nisn_nip' => $validated['nisn_nip'],
            'password' => Hash::make($validated['password'])
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function apiLogout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ], 200);
    }
}
