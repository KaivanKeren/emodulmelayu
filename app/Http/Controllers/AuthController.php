<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
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
}
