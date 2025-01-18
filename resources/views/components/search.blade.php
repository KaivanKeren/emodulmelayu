@props(['placeholder' => 'Search...'])

<div class="relative w-[50%]">
    <label for="global-search" class="sr-only">Search</label>
    <div class="relative">
        {{-- Search Input --}}
        <input type="search" id="global-search" name="search" placeholder="{{ $placeholder }}" autocomplete="off"
            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg 
                   focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 
                   transition-colors duration-200 ease-in-out">
        {{-- Search Icon --}}
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        {{-- Loading Spinner (Hidden by default) --}}
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center hidden" id="search-loading">
            <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>

    {{-- Search Results Dropdown (Hidden by default) --}}
    <div id="search-results"
        class="hidden absolute w-full bg-white mt-1 rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto z-50">
        {{-- Results will be dynamically inserted here --}}
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('global-search');
            const searchResults = document.getElementById('search-results');
            const searchLoading = document.getElementById('search-loading');
            let debounceTimer;

            function showLoading() {
                searchLoading.classList.remove('hidden');
            }

            function hideLoading() {
                searchLoading.classList.add('hidden');
            }

            function renderResults(data) {
                const sections = [{
                        key: 'users',
                        title: 'Pengguna',
                        itemRenderer: user => `
                <a href="/admin/users/${user.id}" class="block px-4 py-2 hover:bg-gray-100">
                    <div class="text-sm font-medium">${user.name}</div>
                    <div class="text-xs text-gray-500">${user.email}</div>
                </a>
            `
                    },
                    {
                        key: 'schools',
                        title: 'Sekolah',
                        itemRenderer: school => `
                <a href="/admin/schools/${school.id}" class="block px-4 py-2 hover:bg-gray-100">
                    <div class="text-sm font-medium">${school.name}</div>
                    <div class="text-xs text-gray-500">${school.address}</div>
                </a>
            `
                    },
                    {
                        key: 'materials',
                        title: 'Materi',
                        itemRenderer: material => `
                <a href="/admin/materials/${material.id}" class="block px-4 py-2 hover:bg-gray-100">
                    <div class="text-sm font-medium">${material.title}</div>
                    <div class="text-xs text-gray-500">${material.description?.substring(0, 100) || ''}...</div>
                </a>
            `
                    },
                    {
                        key: 'assessments',
                        title: 'Assessments',
                        itemRenderer: assessment => `
                <a href="/admin/assessments/${assessment.id}" class="block px-4 py-2 hover:bg-gray-100">
                    <div class="text-sm font-medium">${assessment.title}</div>
                    <div class="text-xs text-gray-500">Category: ${assessment.category}</div>
                </a>
            `
                    },
                    {
                        key: 'discussions',
                        title: 'Diskusi',
                        itemRenderer: discussion => `
                <a href="/admin/discussions/${discussion.id}" class="block px-4 py-2 hover:bg-gray-100">
                    <div class="text-sm font-medium">${discussion.title}</div>
                    <div class="text-xs text-gray-500">${discussion.content?.substring(0, 100) || ''}...</div>
                </a>
            `
                    },
                    {
                        key: 'events',
                        title: 'Event',
                        itemRenderer: event => `
              <a 
                            href="/admin/calendar?tahun=${event.year}&bulan=${event.month}" 
                            onclick="navigateToEvent(event, '${event.date}', ${event.id}, '${event.title}', '${event.content}')"
                            class="block px-4 py-2 hover:bg-gray-100">
                            <div class="text-sm font-medium">${event.title}</div>
                            <div class="text-xs text-gray-500">
                                ${event.content?.substring(0, 100) || ''}...
                            </div>
                            <div class="text-xs text-gray-400">
                                ${new Date(event.date).toLocaleDateString('id-ID')}
                            </div>
                        </a>
            `
                    },
                ];

                let hasResults = false;
                let resultsHtml = '';

                sections.forEach(section => {
                    if (data[section.key]?.length) {
                        hasResults = true;
                        resultsHtml += `
                    <div>
                        <div class="px-4 py-2 bg-gray-50 text-gray-700 font-semibold">${section.title}</div>
                        ${data[section.key].map(section.itemRenderer).join('')}
                    </div>
                `;
                    }
                });

                if (hasResults) {
                    searchResults.innerHTML = resultsHtml;
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = `
                <div class="px-4 py-3 text-sm text-gray-500">
                    No results found
                </div>
            `;
                    searchResults.classList.remove('hidden');
                }
            }

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(debounceTimer);

                if (query === '') {
                    searchResults.classList.add('hidden');
                    hideLoading();
                    return;
                }

                showLoading();
                debounceTimer = setTimeout(() => {
                    fetch(`/admin/search?search=${encodeURIComponent(query)}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'error') {
                                throw new Error(data.message);
                            }
                            hideLoading();
                            renderResults(data);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            hideLoading();
                            searchResults.innerHTML = `
                <div class="px-4 py-3 text-sm text-red-500">
                    An error occurred while searching. Please try again.
                </div>
            `;
                            searchResults.classList.remove('hidden');
                        });
                }, 300);
            });
            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.classList.add('hidden');
                }
            });
        });
    </script>
@endpush
