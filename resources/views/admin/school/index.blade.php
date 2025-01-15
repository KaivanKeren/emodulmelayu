@extends('layouts.admin')

@section('title', 'Sekolah')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Sekolah</h2>
            <div class="flex space-x-3">
                <button class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
                <a href="{{ route('schools.create') }}">
                    <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                        Tambah Sekolah
                    </button>
                </a>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-4">Nama Sekolah</th>
                    <th class="pb-4">Alamat</th>
                    <th class="pb-4">Tanggal Dibuat</th>
                    <th class="pb-4"></th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($schools as $school)
                    <tr class="border-t">
                        <td class="py-4">{{ $school->name }}</td>
                        <td>{{ $school->address }}</td>
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

        <!-- Pagination -->
        <div class="mt-6">
            {{ $schools->links() }}
        </div>
    </div>

    <script>
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
