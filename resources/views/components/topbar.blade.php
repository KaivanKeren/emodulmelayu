<div class="flex justify-between items-center mb-8">
    <div class="relative">
        <input type="text" placeholder="Cari..."
            class="w-96 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <i data-lucide="search" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5"></i>
    </div>
    <div class="flex items-center space-x-4">
        <button class="p-2 text-gray-400 hover:text-gray-500">
            <i data-lucide="bell" class="h-6 w-6"></i>
        </button>
        <span class="text-gray-700">{{ Auth::user()->name }}</span>
    </div>
</div>