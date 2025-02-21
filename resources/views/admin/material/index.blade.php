@extends('layouts.admin')

@section('title', 'Materi')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Materi</h2>
            <div class="flex space-x-3">
                <button onclick="toggleModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
                <a href="{{ route('materials.create') }}">
                    <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                        Tambah Materi
                    </button>
                </a>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-4">Judul</th>
                    <th class="pb-4">Deskripsi</th>
                    <th class="pb-4">Assets</th>
                    <th class="pb-4">Author</th>
                    <th class="pb-4">Tanggal Dibuat</th>
                    <th class="pb-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($materials as $material)
                    <tr class="border-t">
                        <td class="py-4">{{ $material->title }}</td>
                        <td>{{ $material->description }}</td>
                        <td>{{ count(json_decode($material->assets)) }} Files</td>
                        <td>{{ $material->user->name }}</td>
                        <td>{{ $material->created_at->format('d M Y') }}</td>
                        <td class="relative">
                            <button onclick="toggleDropdown({{ $material->id }})" class="text-gray-400 hover:text-gray-500">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div id="dropdown-{{ $material->id }}"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                <div class="py-1">
                                    <a href="{{ route('materials.show', ['material' => $material->id]) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                        Lihat Detail
                                    </a>
                                    <a href="{{ route('materials.edit', ['material' => $material->id]) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                        Edit Materi
                                    </a>
                                    <form action="{{ route('materials.destroy', ['material' => $material->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus material ini?');"
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
                <h3 class="text-lg font-semibold mb-4">Filter Materi</h3>
                <form action="{{ route('materials.filter') }}" method="GET">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                        <input type="text" id="title" name="title"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Author</label>
                        <input type="text" id="name" name="name"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <input type="text" id="description" name="description"
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

    <div class="mt-6">
        {{ $materials->links() }}
    </div>

    <!-- JavaScript for dropdown functionality -->
<script>
    function toggleModal() {
        const modal = document.getElementById('filterModal');
        modal.classList.toggle('hidden');
    }

    function toggleDropdown(materialId) {
        // Close all other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            if (dropdown.id !== `dropdown-${materialId}`) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle current dropdown
        const dropdown = document.getElementById(`dropdown-${materialId}`);
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

    function truncateText(text, maxLength) {
        if (text.length > maxLength) {
            return text.substring(0, maxLength) + '...';
        }
        return text;
    }

    // After the page loads, truncate content in the table
    document.addEventListener('DOMContentLoaded', function() {
        // Get all title and description cells
        const titleCells = document.querySelectorAll('td:nth-child(1)');
        const descriptionCells = document.querySelectorAll('td:nth-child(2)');

        // Truncate titles to 30 characters
        titleCells.forEach(cell => {
            const originalText = cell.textContent;
            cell.textContent = truncateText(originalText, 30);

            // Add title attribute to show full text on hover
            cell.title = originalText;
        });

        // Truncate descriptions to 50 characters
        descriptionCells.forEach(cell => {
            const originalText = cell.textContent;
            cell.textContent = truncateText(originalText, 50);

            // Add title attribute to show full text on hover
            cell.title = originalText;
        });
    });
</script>@endsection
