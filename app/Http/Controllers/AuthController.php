<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Web methods
    public function login()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Ambil user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Validasi kredensial dan status pengguna
        if ($user) {
            if ($user->status === 'pending') {
                return back()->with('error', 'Akun Anda belum disetujui. Silakan hubungi administrator.');
            }

            if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
                // Redirect berdasarkan role pengguna
                switch (auth()->user()->role) {
                    case 'siswa':
                        return redirect()->route('studentDashboard');
                    case 'guru':
                        return redirect()->route('teacherDashboard');
                    case 'admin':
                        return redirect()->route('adminDashboard');
                    default:
                        return redirect()->route('dashboard');
                }
            }
        }

        // Jika kredensial tidak valid
        return back()->with('error', 'Kredensial tidak valid.');
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('login');
    }

    // API methods
    public function apiLogin(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Email tidak valid.',
                'email.string' => 'Format email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.string' => 'Format kata sandi tidak valid.',
            ]);

            // Cek kredensial
            if (!Auth::attempt($validated)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau kata sandi tidak valid.'
                ], 401);
            }

            // Ambil data user
            $user = Auth::user();

            // Validasi status pengguna
            if ($user->status === 'pending') {
                Auth::logout(); // Logout user yang statusnya pending
                return response()->json([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Akun Anda belum disetujui. Silakan hubungi administrator.',
                ], 403);
            }

            // Buat token
            $token = $user->createToken('auth-token');

            return response()->json([
                'code' => 200,
                'message' => 'Login berhasil.',
                'data' => [
                    'token' => $token->plainTextToken,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'school' => $user->school,
                        'role' => $user->role,
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            // Tangani validasi gagal
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Logging error lain
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->input('email', 'not provided'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }

    public function apiRegister(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'school_id' => 'required|exists:schools,id',
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
                'password' => 'required|min:6',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'school_id.required' => 'ID sekolah wajib diisi.',
                'school_id.exists' => 'Sekolah tidak ditemukan.',
                'nisn_nip.required' => 'NISN/NIP wajib diisi.',
                'nisn_nip.unique' => 'NISN/NIP sudah terdaftar.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.min' => 'Kata sandi minimal 6 karakter.',
            ]);

            // Determine role based on NISN/NIP length
            $role = strlen($validated['nisn_nip']) === 10 ? 'siswa' : 'guru';

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'school_id' => $validated['school_id'],
                'nisn_nip' => $validated['nisn_nip'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
            ]);

            // Retrieve school name
            $schoolName = School::find($validated['school_id'])->name;

            return response()->json([
                'code' => 201,
                'message' => 'Registrasi berhasil.',
                'data' => [
                    'user' => $user,
                    'school_name' => $schoolName,
                ],
            ], 201)->header('Accept', 'application/json');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            // Log unexpected errors for debugging
            Log::error('An unexpected error occurred:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }


    public function apiLogout(): JsonResponse
    {
        try {
            // Pastikan pengguna sudah login
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan atau sudah logout.',
                ], 401);
            }

            // Hapus semua token pengguna
            $user->tokens()->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Berhasil logout.',
                'data' => new \stdClass(),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}
