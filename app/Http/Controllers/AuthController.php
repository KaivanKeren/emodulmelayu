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
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Invalid credentials');
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
                        $fail('The NISN/NIP must be either 10 digits (for students) or 18 digits (for teachers).');
                    }
                },
            ],
            'password' => 'required|min:6|confirmed',
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
            ->with('success', 'User created successfully.');
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
            'message' => 'Invalid credentials'
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