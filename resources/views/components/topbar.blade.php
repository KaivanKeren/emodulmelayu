    <div class="max-w-7xl top-0 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Search Bar Section --}}
            <div class="flex-1 max-w-lg">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <input type="search" id="search" name="search" placeholder="Search..."
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                               focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 transition-colors
                               duration-200 ease-in-out">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Right Side Section --}}
            <div class="flex items-center space-x-6">
                {{-- Notification Bell --}}
                <div class="relative">
                    <button type="button"
                        class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none 
                               focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        aria-label="Notifications">
                        <span
                            class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 flex items-center justify-center">
                            <span class="text-xs text-white">3</span>
                        </span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                </div>

                {{-- User Profile Section --}}
                <div class="flex items-center space-x-3">
                    <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
                        alt="{{ Auth::user()->name }}" class="h-8 w-8 rounded-full object-cover border border-gray-200">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-700">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ Auth::user()->email }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
