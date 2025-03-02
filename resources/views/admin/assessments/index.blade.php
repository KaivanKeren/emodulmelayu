@extends('layouts.admin')

@section('title', 'Assessment')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Assessment</h2>
            <div class="flex space-x-3">
                <button onclick="toggleModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
                <a href="{{ route('assessments.create') }}">
                    <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                        Tambah Assessment
                    </button>
                </a>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-4">
                        <div class="flex items-center">
                            Judul
                            <a href="{{ route('assessments.index', ['sort' => 'title', 'direction' => (request('sort') == 'title' && request('direction') == 'asc') || (!request('sort') && !request('direction')) ? 'desc' : 'asc']) }}"
                                class="ml-1">
                                @if (request('sort') == 'title' || (!request('sort') && !request('direction')))
                                    <i data-lucide="{{ (request('sort') == 'title' && request('direction') == 'asc') || (!request('sort') && !request('direction')) ? 'arrow-up' : 'arrow-down' }}"
                                        class="w-4 h-4"></i>
                                @else
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-gray-300"></i>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th class="pb-4">Kategori</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Tanggal Dibuat</th>
                    <th class="pb-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($assessments as $assessment)
                    <tr class="border-t">
                        <td class="py-4">{{ $assessment->title }}</td>
                        <td>{{ $assessment->category }}</td>
                        <td>
                            <span
                                class="px-2 py-1 text-xs font-medium 
                            @if ($assessment->status === 'Terbuka') text-green-700 bg-green-100
                            @elseif ($assessment->status === 'Belum Terbuka') text-yellow-700 bg-yellow-100
                            @else text-red-700 bg-red-100 @endif 
                            rounded-full">
                                {{ $assessment->status }}
                            </span>
                        </td>
                        <td>{{ $assessment->created_at->format('d M Y') }}</td>
                        <td class="relative">
                            <button onclick="toggleDropdown({{ $assessment->id }})"
                                class="text-gray-400 hover:text-gray-500">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div id="dropdown-{{ $assessment->id }}"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                <div class="py-1">
                                    <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                        Lihat Detail
                                    </a>
                                    <a href="{{ route('questions.create', ['assessment' => $assessment->id]) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                                        Tambah Pertanyaan
                                    </a>
                                    <a href="{{ route('assessments.edit', ['assessment' => $assessment->id]) }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                        Edit Assessment
                                    </a>
                                    <form action="{{ route('assessments.destroy', ['assessment' => $assessment->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus assessment ini?');"
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
                <h3 class="text-lg font-semibold mb-4">Filter Assessment</h3>
                <form action="{{ route('assessments.filter') }}" method="GET">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                        <input type="text" id="title" name="title"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <input type="text" id="category" name="category"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status"
                            class="w-full mt-1 px-3 py-2 border rounded-lg shadow-sm focus:ring focus:ring-blue-300">
                            <option value="">Semua</option>
                            <option value="Belum Terbuka">Belum Terbuka</option>
                            <option value="Terbuka">Terbuka</option>
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

    <div class="mt-6">
        {{ $assessments->links() }}
    </div>

    <!-- JavaScript for dropdown functionality -->
    <script>
        function toggleModal() {
            const modal = document.getElementById('filterModal');
            modal.classList.toggle('hidden');
        }

        function toggleDropdown(assessmentId) {
            // Close all other dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                if (dropdown.id !== `dropdown-${assessmentId}`) {
                    dropdown.classList.add('hidden');
                }
            });

            // Toggle current dropdown
            const dropdown = document.getElementById(`dropdown-${assessmentId}`);
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
