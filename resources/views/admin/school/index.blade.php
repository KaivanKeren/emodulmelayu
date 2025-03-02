@extends('layouts.admin')

@section('title', 'Sekolah')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Sekolah</h2>
            <div class="flex space-x-3">
                <button onclick="toggleModal()"
                    class="px-3 md:px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
                <a href="{{ route('schools.create') }}">
                    <button class="px-3 md:px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                        Tambah Sekolah
                    </button>
                </a>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-4">
                        <div class="flex items-center">
                            Nama Sekolah
                            <a href="{{ route('schools.index', ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                class="ml-1">
                                @if (request('sort') == 'name')
                                    <i data-lucide="{{ request('direction') == 'asc' ? 'arrow-up' : 'arrow-down' }}"
                                        class="w-4 h-4"></i>
                                @else
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-gray-300"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th class="pb-4">Alamat</th>
                    <th class="pb-4">Tanggal Dibuat</th>
                    <th class="pb-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($schools as $school)
                    <tr class="border-t">
                        <td class="py-4">{{ $school->name }}</td>
                        <td>{{ $school->address ?? 'Tidak ada alamat' }}</td>
                        <td>{{ $school->created_at->format('d M Y') }}</td>
                        <td>
                            <button onclick="toggleDropdown({{ $school->id }})" class="text-gray-400 hover:text-gray-500">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                            <div id="dropdown-{{ $school->id }}"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                <div class="py-1">
                                    @if ($school->status === 'Pending')
                                        <a href="{{ route('users.accept', $school->id) }}"
                                            class="flex items-center px-4 py-2 text-sm text-green-600 hover:bg-gray-100">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                            Terima
                                        </a>
                                    @endif
                                    <a href="{{ route('schools.edit', $school->id) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                        Edit Sekolah
                                    </a>
                                    <form action="{{ route('schools.destroy', ['school' => $school->id]) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus sekolah ini?');"
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div id="filterModal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                <h3 class="text-lg font-semibold mb-4">Filter Sekolah</h3>
                <form action="{{ route('schools.filter') }}" method="GET">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                        <input type="text" id="name" name="name"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <input type="text" id="address" name="address"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
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
        {{ $schools->links() }}
    </div>

    <script>
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

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
            dropdowns.forEach(dropdown => {
                if (!event.target.closest('td')) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
