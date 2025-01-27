@extends('layouts.admin')

@section('title', 'Filter Pesan')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Kata yang Difilter</h1>

                <!-- Add New Word Button -->
                <button onclick="document.getElementById('addWordModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Tambahkan kata baru
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Word
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Dibuat</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($filterWords as $word)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $word->word }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $word->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <form action="{{ route('filter-message.destroy', $word) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this word?')"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No filtered words found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($filterWords->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $filterWords->links() }}
                </div>
            @endif
        </div>

        <!-- Add Word Modal -->
        <div id="addWordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex flex-col">
                    <div class="flex justify-between items-center pb-3">
                        <p class="text-lg font-semibold">Add New Filtered Word</p>
                        <button onclick="document.getElementById('addWordModal').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('filter-message.store') }}" method="POST">
                        @csrf
                        <div class="mt-2">
                            <input type="text" name="word" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter word to filter">
                        </div>

                        <div class="mt-4 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('addWordModal').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Tambahkan kata
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div id="flashMessage"
            class="fixed bottom-4 right-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('flashMessage').remove();
            }, 3000);
        </script>
    @endif
@endsection
