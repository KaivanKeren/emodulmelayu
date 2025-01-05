<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Modul Budaya Melayu Riau - Register</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <!-- Main Container -->
        <div class="w-full max-w-4xl space-y-6 bg-white p-8 rounded-2xl shadow-lg">
            <!-- Logo/Image Container -->
            <div class="flex items-center space-x-8">
                <!-- Right side - Text -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">
                        Registrasi E-Modul
                    </h2>
                    <h3 class="text-xl font-semibold text-gray-900">
                        Budaya Melayu Riau
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Silahkan lengkapi data diri Anda dengan benar
                    </p>
                </div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Terdapat beberapa kesalahan:
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Registration Form -->
            <form action="{{ route('postRegister') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Name Input -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('name') border-red-500 @enderror">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                    </svg>
                                </span>
                                <input id="name" name="name" type="text" required 
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="Nama Lengkap"
                                    value="{{ old('name') }}">
                            </div>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Input -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('email') border-red-500 @enderror">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </span>
                                <input id="email" name="email" type="email" required 
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="Email"
                                    value="{{ old('email') }}">
                            </div>
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- School Select -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('school') border-red-500 @enderror">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762z"/>
                                    </svg>
                                </span>
                                <select id="school" name="school" required
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                    <option value="">Pilih Sekolah</option>
                                    <option value="smk 1 riau" {{ old('school') == 'smk 1 riau' ? 'selected' : '' }}>SMK 1 RIAU</option>
                                    <option value="smk 2 riau" {{ old('school') == 'smk 2 riau' ? 'selected' : '' }}>SMK 2 RIAU</option>
                                    <option value="smk 3 riau" {{ old('school') == 'smk 3 riau' ? 'selected' : '' }}>SMK 3 RIAU</option>
                                </select>
                            </div>
                            @error('school')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- NISN/NIP Input -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('nisn_nip') border-red-500 @enderror">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <input id="nisn_nip" name="nisn_nip" type="number" required 
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="NISN/NIP"
                                    value="{{ old('nisn_nip') }}">
                            </div>
                            @error('nisn_nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 @error('password') border-red-500 @enderror">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input id="password" name="password" type="password" required 
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="Password">
                            </div>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation Input -->
                        <div>
                            <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input id="password_confirmation" name="password_confirmation" type="password" required 
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="Konfirmasi Password">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Register Button and Login Link -->
                <div class="mt-8 space-y-4">
                    <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 rounded-full text-white text-sm font-medium bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Daftar
                    </button>
                    
                    <div class="flex items-center justify-center text-sm">
                        <span class="text-gray-500">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="ml-2 font-medium text-orange-500 hover:text-orange-600">
                            Masuk
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>