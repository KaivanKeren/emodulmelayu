@extends('layouts.admin')

@section('title', 'User')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Pengguna</h2>
            <div class="flex space-x-3">
                @if ($users->where('status', 'Pending')->count() > 0)
                    <form action="{{ route('users.accept-all') }}" method="POST" class="inline" id="acceptAllForm">
                        @csrf
                        <button type="button" onclick="confirmAcceptAll()"
                            class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Terima Semua
                        </button>
                    </form>
                @endif
                <button onclick="toggleModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
            </div>
        </div>
        <form id="bulkAcceptForm" action="{{ route('users.bulk-accept') }}" method="POST">
            @csrf
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm">
                        <th class="pb-4">Nama</th>
                        <th class="pb-4">Email</th>
                        <th class="pb-4">Role</th>
                        <th class="pb-4">Sekolah</th>
                        <th class="pb-4">Status</th>
                        <th class="pb-4">Tanggal Daftar</th>
                        <th class="pb-4">Password</th>
                        <th class="pb-4">Aksi</th>
                        <th class="pb-4">Terima</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="py-4">{{ $user->name }}</td>
                            <td class="py-4">{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->school->name }}</td>
                            <td>
                                <span
                                    class="px-2 py-1 text-xs font-medium 
                                    @if ($user->status === 'Accepted') text-green-700 bg-green-100
                                    @elseif ($user->status === 'Pending') text-yellow-700 bg-yellow-100
                                    @else text-red-700 bg-red-100 @endif 
                                    rounded-full">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td class="flex justify-center" id="password-{{ $user->id }}">
                                <button type="button" onclick="showPassword('{{ $user->id }}')"
                                    class="mt-3 text-blue-500 hover:text-blue-700">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </td>

                            <td class="py-4">
                                <div class="flex space-x-2">
                                    <button type="button" onclick="toggleDropdown({{ $user->id }})"
                                        class="text-gray-400 hover:text-gray-500">
                                        <i data-lucide="more-vertical" class="w-5 h-5"></i>
                                    </button>
                                    <div id="dropdown-{{ $user->id }}"
                                        class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                        <div class="py-1">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                                Edit Pengguna
                                            </a>
                                            <form action="{{ route('users.destroy', ['user' => $user->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($user->status === 'Pending')
                                    <a href="{{ route('users.accept', $user->id) }}" class="text-sm text-green-600">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>

        <!-- Filter Modal -->
        <div id="filterModal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                <h3 class="text-lg font-semibold mb-4">Filter Pengguna</h3>
                <form action="{{ route('users.page.filter') }}" method="GET">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" id="name" name="name"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                            <option value="">Semua</option>
                            <option value="Admin">Admin</option>
                            <option value="Siswa">Siswa</option>
                            <option value="Guru">Guru</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="school" class="block text-sm font-medium text-gray-700">Sekolah</label>
                        <input type="text" id="school" name="school"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                            <option value="">Semua</option>
                            <option value="Accepted">Accepted</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal()"
                            class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->links() }}
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmAcceptAll() {
            if (confirm('Apakah Anda yakin ingin menerima semua pengguna yang pending?')) {
                document.getElementById('acceptAllForm').submit();
            }
        }

        function toggleModal() {
            const modal = document.getElementById('filterModal');
            modal.classList.toggle('hidden');
        }

        function toggleDropdown(userId) {
            // Close all other dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                if (dropdown.id !== `dropdown-${userId}`) {
                    dropdown.classList.add('hidden');
                }
            });

            // Toggle current dropdown
            const dropdown = document.getElementById(`dropdown-${userId}`);
            dropdown.classList.toggle('hidden');
        }

        // Handle select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.getElementsByClassName('user-checkbox');
            for (let checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
            dropdowns.forEach(dropdown => {
                if (!event.target.closest('td')) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        function showPassword(userId) {
            $.ajax({
                url: `/admin/users/password/${userId}`,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        const passwordElement = document.getElementById('password-' + userId);
                        if (passwordElement) {
                            // Check if the password is currently visible
                            if (passwordElement.innerHTML.includes('Hide')) {
                                // If it's visible, hide it
                                passwordElement.innerHTML = `
                            <button type="button" onclick="showPassword('${userId}')"
                                class="text-blue-500 hover:text-blue-700">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        `;
                            } else {
                                // If it's hidden, show it
                                passwordElement.innerHTML = `
                            <span class="mt-3 text-sm font-medium">${response.password}</span>
                            <button type="button" onclick="showPassword('${userId}')"
                                class="text-red-500 hover:text-red-700">
                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                            </button>
                        `;
                            }
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat mengambil data password.');
                }
            });
        }
    </script>
@endsection
