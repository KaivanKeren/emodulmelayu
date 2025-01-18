    <div class="max-w-7xl top-0 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Search Bar Section --}}
            <x-search placeholder="Cari semuanya..." />

            {{-- Right Side Section --}}
            <div class="flex items-center space-x-6">
                {{-- Notification Bell --}}
                <div class="relative">
                    <!-- Tombol Notifikasi -->
                    <button type="button" onclick="toggleNotifList()"
                        class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        aria-label="Notifications">
                        <span id="notifBadge"
                            class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 flex items-center justify-center">
                            <span class="text-xs text-white" id="notifCount">3</span>
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
    <!-- Modal Notifikasi -->
    <div id="notifModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white transform transition-all">
            <div class="mt-2">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center space-x-3">
                        <div id="notifIcon" class="text-2xl"></div>
                        <h3 id="notifTitle" class="text-xl font-semibold text-gray-900">Notifikasi</h3>
                    </div>
                    <button onclick="closeNotifModal()"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div id="notifList" class="space-y-3">
                        <!-- Notifikasi akan di-render di sini -->
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="closeNotifModal()"
                        class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data notifikasi (contoh)
        let notifications = [{
                id: 1,
                type: 'success',
                message: 'Data berhasil disimpan',
                time: '1 menit yang lalu'
            },
            {
                id: 2,
                type: 'warning',
                message: 'Pembayaran akan jatuh tempo',
                time: '5 menit yang lalu'
            },
            {
                id: 3,
                type: 'info',
                message: 'Ada pembaruan sistem',
                time: '10 menit yang lalu'
            }
        ];

        // Fungsi untuk mendapatkan icon berdasarkan tipe
        function getNotifIcon(type) {
            switch (type) {
                case 'success':
                    return '✅';
                case 'error':
                    return '❌';
                case 'warning':
                    return '⚠️';
                case 'info':
                    return 'ℹ️';
                default:
                    return 'ℹ️';
            }
        }

        // Fungsi untuk memperbarui badge count
        function updateNotifCount() {
            const badge = document.getElementById('notifBadge');
            const count = document.getElementById('notifCount');
            if (notifications.length > 0) {
                badge.classList.remove('hidden');
                count.textContent = notifications.length;
            } else {
                badge.classList.add('hidden');
            }
        }

        // Fungsi untuk menampilkan daftar notifikasi
        function renderNotifications() {
            const notifList = document.getElementById('notifList');
            notifList.innerHTML = notifications.map(notif => `
        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
            <div class="text-xl">${getNotifIcon(notif.type)}</div>
            <div class="flex-1">
                <p class="text-gray-700">${notif.message}</p>
                <p class="text-sm text-gray-500 mt-1">${notif.time}</p>
            </div>
            <button onclick="removeNotification(${notif.id})" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `).join('');
        }

        // Fungsi untuk menampilkan modal notifikasi
        function toggleNotifList() {
            const modal = document.getElementById('notifModal');
            modal.classList.remove('hidden');
            renderNotifications();
        }

        // Fungsi untuk menutup modal
        function closeNotifModal() {
            const modal = document.getElementById('notifModal');
            modal.classList.add('hidden');
        }

        // Fungsi untuk menghapus notifikasi
        function removeNotification(id) {
            notifications = notifications.filter(notif => notif.id !== id);
            renderNotifications();
            updateNotifCount();
        }

        // Fungsi untuk menambah notifikasi baru
        function addNotification(type, message) {
            const newNotif = {
                id: Date.now(),
                type,
                message,
                time: 'Baru saja'
            };
            notifications.unshift(newNotif);
            updateNotifCount();
            renderNotifications();
        }

        // Inisialisasi
        updateNotifCount();
    </script>
