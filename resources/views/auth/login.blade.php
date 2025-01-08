<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Modul Budaya Melayu Riau - Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        window.onload = () => lucide.createIcons();
    </script>
    <style>
        .bg-auth-pattern {
            background-image: url('/bg-auth.jpg');
            background-repeat: repeat;
            background-size: 300px;
            opacity: 0.2;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Background Pattern -->
    <div class="bg-auth-pattern"></div>

    <div class="min-h-screen flex flex-col items-center justify-center px-4 relative z-10">
        <!-- Main Container -->
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-lg">
            <!-- Logo/Image Container -->
            <div class="flex flex-col items-center">
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">
                    Selamat datang di E-Modul
                </h2>
                <h3 class="text-xl font-semibold text-gray-900 text-center">
                    Budaya Melayu Riau
                </h3>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Silahkan masukkan Email dan Password dengan benar!
                </p>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form class="mt-8 space-y-6" action="{{ route('postLogin') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="rounded-md">
                    <div
                        class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('email') border-red-500 @enderror">
                        <span class="text-gray-400">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input id="email" name="email" type="email" required
                            class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                            placeholder="Email" value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="rounded-md">
                    <div
                        class="relative flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('password') border-red-500 @enderror">
                        <span class="text-gray-400">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </span>
                        <input id="password" name="password" type="password" required
                            class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                            placeholder="Password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i data-lucide="eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Login Button -->
                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 rounded-full text-white text-sm font-medium bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Masuk
                    </button>
                </div>

                <!-- Register Link -->
                <div class="flex items-center justify-center text-sm">
                    <span class="text-gray-500">Belum mempunyai akun?</span>
                    <a href="{{ route('register') }}" class="ml-2 font-medium text-orange-500 hover:text-orange-600">
                        Registrasi
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.setAttribute('data-lucide', 'eye-off');
        } else {
            passwordInput.type = 'password';
            toggleIcon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons(); // Re-render icons
    }
</script>

</html>
