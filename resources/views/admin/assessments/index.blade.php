@extends('layouts.admin')

@section('title', 'Assessment')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Assessment</h2>
            <div class="flex space-x-3">
                <button class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
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
                    <th class="pb-4">Judul</th>
                    <th class="pb-4">Kategori</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Tanggal Dibuat</th>
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
                            @if ($assessment->status === 'terbuka') text-green-700 bg-green-100
                            @elseif ($assessment->status === 'belum terbuka') text-yellow-700 bg-yellow-100
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
                                        Buat Pertanyaan
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
    </div>

    <!-- JavaScript for dropdown functionality -->
    <script>
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
