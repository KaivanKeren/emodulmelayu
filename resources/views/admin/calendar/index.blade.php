@extends('layouts.admin')

@section('title', 'Kalender')

@section('content')
    <style>
        .calendar-cell:hover .event-tooltip {
            display: block;
            overflow: hidden;
        }

        .event-tooltip {
            display: none;
            position: absolute;
            z-index: 10;
        }

        /* Custom scrollbar for webkit browsers */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <div class="w-full max-w-6xl mx-auto bg-white p-6 shadow-2xl rounded-2xl">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 space-y-4 sm:space-y-0">
            <!-- Previous Month Button -->
            <button onclick="navigate(-1)"
                class="group px-4 py-2 text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Sebelumnya</span>
            </button>

            <!-- Month/Year Selection -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-800 mb-4 tracking-tight">
                    {{ $tanggalSaatIni->locale('id')->translatedFormat('F Y') }}
                </h1>
                <div class="flex space-x-4">
                    <select id="bulan"
                        class="form-select block w-full pl-4 pr-10 py-2.5 text-base border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 rounded-xl bg-white transition-colors duration-200"
                        onchange="updateKalender()">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $tanggalSaatIni->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select id="tahun"
                        class="form-select block w-full pl-4 pr-10 py-2.5 text-base border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 rounded-xl bg-white transition-colors duration-200"
                        onchange="updateKalender()">
                        @foreach (range($tanggalSaatIni->year - 10, $tanggalSaatIni->year + 10) as $y)
                            <option value="{{ $y }}" {{ $tanggalSaatIni->year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Next Month Button -->
            <button onclick="navigate(1)"
                class="group px-4 py-2 text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center space-x-2">
                <span>Berikutnya</span>
                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
            <!-- Days Header -->
            <div class="grid grid-cols-7 bg-gradient-to-r from-orange-50 to-orange-100">
                @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                    <div class="py-4 text-center text-sm font-semibold text-gray-700">
                        {{ $hari }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-px bg-gray-200">
                @foreach ($kalender as $minggu)
                    @foreach ($minggu as $hari)
                        <div class="min-h-[120px] bg-white relative group calendar-cell">
                            @if ($hari)
                                <button
                                    onclick="openEventModal('{{ $tanggalSaatIni->year }}-{{ str_pad($tanggalSaatIni->month, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($hari, 2, '0', STR_PAD_LEFT) }}')"
                                    class="w-full h-full flex flex-col hover:bg-orange-50 rounded-lg p-3 transition-colors duration-300
                                    {{ $tanggalSaatIni->year == now()->year && $tanggalSaatIni->month == now()->month && $hari == now()->day
                                        ? 'bg-orange-50'
                                        : '' }}">
                                    <span
                                        class="text-sm font-medium inline-flex items-center justify-center w-8 h-8 
                                        {{ $tanggalSaatIni->year == now()->year && $tanggalSaatIni->month == now()->month && $hari == now()->day
                                            ? 'text-white bg-orange-500 rounded-full shadow-lg'
                                            : 'text-gray-700' }}">
                                        {{ $hari }}
                                    </span>
                                    <!-- Event List -->
                                    <div class="mt-2 space-y-1.5 max-h-20 overflow-y-auto custom-scrollbar">
                                        @php
                                            $dateKey = sprintf(
                                                '%s-%s-%s',
                                                $tanggalSaatIni->year,
                                                str_pad($tanggalSaatIni->month, 2, '0', STR_PAD_LEFT),
                                                str_pad($hari, 2, '0', STR_PAD_LEFT),
                                            );
                                        @endphp

                                        @if (isset($events[$dateKey]))
                                            @foreach ($events[$dateKey] as $event)
                                                <div class="group/event relative">
                                                    <div onclick="openEventModal('{{ $dateKey }}', { id: {{ $event->id }}, title: '{{ addslashes($event->title) }}', content: '{{ addslashes($event->content) }}' })"
                                                        class="px-2.5 py-1.5 text-xs bg-orange-100 text-orange-800 rounded-lg truncate hover:bg-orange-200 cursor-pointer transition-colors duration-200 shadow-sm">
                                                        {{ $event->title }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </button>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="eventModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white transform transition-all">
            <div class="mt-2">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-900">Tambah Event</h3>
                    <button onclick="closeEventModal()"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="eventForm" onsubmit="submitEvent(event)" class="space-y-6">
                    <input type="hidden" id="eventDate" name="date">
                    <input type="hidden" id="eventId" name="id">

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Event</label>
                        <div class="relative">
                            <input type="text" id="title" name="title" required
                                class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 transition-colors duration-200"
                                placeholder="Masukkan judul event">
                        </div>
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <div class="relative">
                            <textarea id="content" name="content" rows="4"
                                class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 transition-colors duration-200"
                                placeholder="Tambahkan deskripsi event"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" id="deleteButton" onclick="deleteEvent()"
                            class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hidden">
                            Hapus
                        </button>
                        <button type="button" onclick="closeEventModal()"
                            class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit" id="submitButton"
                            class="px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function navigate(arah) {
            const urlParams = new URLSearchParams(window.location.search);
            let tahun = parseInt(urlParams.get('tahun')) || {{ $tanggalSaatIni->year }};
            let bulan = parseInt(urlParams.get('bulan')) || {{ $tanggalSaatIni->month }};

            bulan += arah;
            if (bulan > 12) {
                bulan = 1;
                tahun++;
            } else if (bulan < 1) {
                bulan = 12;
                tahun--;
            }

            window.location.href = `?tahun=${tahun}&bulan=${bulan}`;
        }

        function updateKalender() {
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            window.location.href = `?tahun=${tahun}&bulan=${bulan}`;
        }

        // Event Modal Functions
        function openEventModal(date) {
            document.getElementById('eventModal').classList.remove('hidden');
            document.getElementById('eventDate').value = date;
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            document.getElementById('eventForm').reset();
        }

        async function submitEvent(e) {
            e.preventDefault();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page.');
                return;
            }

            const formData = new FormData(document.getElementById('eventForm'));

            try {
                const response = await fetch('/admin/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message || 'Event berhasil ditambahkan');
                    closeEventModal();
                    window.location.reload();
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert(errorMessages);
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat menyimpan event');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan event. Silakan coba lagi.');
            }
        }

        // Navigasi keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                navigate(-1);
            } else if (e.key === 'ArrowRight') {
                navigate(1);
            } else if (e.key === 'Escape') {
                closeEventModal();
            }
        });
    </script>
    <script>
        let currentEventId = null;

        async function openEventModal(date, event = null) {
            const modal = document.getElementById('eventModal');
            const modalTitle = document.getElementById('modalTitle');
            const deleteButton = document.getElementById('deleteButton');
            const form = document.getElementById('eventForm');

            // Reset form
            form.reset();
            document.getElementById('eventDate').value = date;

            if (event) {
                try {
                    // Fetch complete event data for editing
                    const response = await fetch(`/admin/events/${event.id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch event details');
                    }

                    const data = await response.json();
                    const eventData = data.data; // Access the event data from the response

                    // Edit mode
                    modalTitle.textContent = 'Edit Event';
                    document.getElementById('eventId').value = eventData.id;
                    document.getElementById('title').value = eventData.title;
                    document.getElementById('content').value = eventData.content || '';
                    deleteButton.classList.remove('hidden');
                    currentEventId = eventData.id;
                } catch (error) {
                    console.error('Error:', error);
                    alert('Gagal memuat detail event. Silakan coba lagi.');
                    return;
                }
            } else {
                // Create mode
                modalTitle.textContent = 'Tambah Event';
                document.getElementById('eventId').value = '';
                deleteButton.classList.add('hidden');
                currentEventId = null;
            }

            modal.classList.remove('hidden');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            document.getElementById('eventForm').reset();
            currentEventId = null;
        }

        async function submitEvent(e) {
            e.preventDefault();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page.');
                return;
            }

            const formData = new FormData(document.getElementById('eventForm'));
            const isEdit = currentEventId !== null;
            const url = isEdit ? `/admin/events/${currentEventId}` : '/admin/events';

            // Convert FormData to JSON object
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message || `Event berhasil ${isEdit ? 'diperbarui' : 'ditambahkan'}`);
                    closeEventModal();
                    window.location.reload();
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert(errorMessages);
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat menyimpan event');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        }

        async function deleteEvent() {
            if (!currentEventId) return;

            if (!confirm('Apakah Anda yakin ingin menghapus event ini?')) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page.');
                return;
            }

            try {
                const response = await fetch(`/admin/events/${currentEventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message || 'Event berhasil dihapus');
                    closeEventModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan saat menghapus event');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus event. Silakan coba lagi.');
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeEventModal();
            }
        });
    </script>
@endsection
