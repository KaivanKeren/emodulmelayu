@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Pengguna</h2>
                    </div>

                    <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="user" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="name" id="name" placeholder="Nama"
                                        value="{{ old('name', $user->name) }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="mail" class="w-5 h-5"></i>
                                    </span>
                                    <input type="email" name="email" id="email" placeholder="Email"
                                        value="{{ old('email', $user->email) }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NISN/NIP -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="id-card" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="nisn_nip" id="nisn_nip" placeholder="NISN/NIP"
                                        value="{{ old('nisn_nip', $user->nisn_nip) }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('nisn_nip')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Role -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="users" class="w-5 h-5"></i>
                                    </span>
                                    <select name="role" id="role"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                        <option value="">Pilih Role</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                            Admin</option>
                                        <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>
                                            Guru</option>
                                        <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>
                                            Siswa</option>
                                    </select>
                                </div>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- School -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="building" class="w-5 h-5"></i>
                                    </span>
                                    <select name="school" id="school"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                        <option value="">Pilih Sekolah</option>
                                        <option value="smk 1 riau"
                                            {{ old('school', $user->school) == 'smk 1 riau' ? 'selected' : '' }}>SMK 1 Riau
                                        </option>
                                        <option value="smk 2 riau"
                                            {{ old('school', $user->school) == 'smk 2 riau' ? 'selected' : '' }}>SMK 2 Riau
                                        </option>
                                        <option value="smk 3 riau"
                                            {{ old('school', $user->school) == 'smk 3 riau' ? 'selected' : '' }}>SMK 3 Riau
                                        </option>
                                    </select>
                                </div>
                                @error('school')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="key" class="w-5 h-5"></i>
                                    </span>
                                    <input type="password" name="password" id="password"
                                        placeholder="Password (Kosongkan jika tidak ingin mengubah)"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('users.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
