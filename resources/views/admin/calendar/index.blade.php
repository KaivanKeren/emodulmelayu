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

        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #555; }

        @media (max-width: 640px) {
            .calendar-cell { min-height: 80px !important; }
            .event-title { font-size: 0.65rem; }
        }

        @media (max-width: 480px) {
            .calendar-day-header { font-size: 0.75rem; padding: 0.5rem 0; }
        }

        /* Badge event berulang */
        .event-recurring {
            background: linear-gradient(135deg, #fed7aa, #fdba74);
            border-left: 2px solid #f97316;
        }

        .event-recurring:hover {
            background: linear-gradient(135deg, #fdba74, #fb923c) !important;
        }

        /* Toggle switch */
        .toggle-checkbox:checked { right: 0; border-color: #f97316; }
        .toggle-checkbox:checked + .toggle-label { background-color: #f97316; }
    </style>

    <div class="w-full max-w-6xl mx-auto bg-white p-3 sm:p-6 shadow-2xl rounded-2xl">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 sm:mb-8 space-y-4 sm:space-y-0">
            <button onclick="navigate(-1)"
                class="group px-3 sm:px-4 py-2 text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center space-x-1 sm:space-x-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transform group-hover:-translate-x-1 transition-transform duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Sebelumnya</span>
            </button>

            <div class="text-center w-full sm:w-auto px-2">
                <h1 class="text-2xl sm:text-4xl font-bold text-gray-800 mb-2 sm:mb-4 tracking-tight">
                    {{ $tanggalSaatIni->locale('id')->translatedFormat('F Y') }}
                </h1>
                <div class="flex space-x-2 sm:space-x-4">
                    <select id="bulan"
                        class="form-select block w-full pl-2 sm:pl-4 pr-8 sm:pr-10 py-1.5 sm:py-2.5 text-sm sm:text-base border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 rounded-xl bg-white transition-colors duration-200"
                        onchange="updateKalender()">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $tanggalSaatIni->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select id="tahun"
                        class="form-select block w-full pl-2 sm:pl-4 pr-8 sm:pr-10 py-1.5 sm:py-2.5 text-sm sm:text-base border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 rounded-xl bg-white transition-colors duration-200"
                        onchange="updateKalender()">
                        @foreach (range($tanggalSaatIni->year - 10, $tanggalSaatIni->year + 10) as $y)
                            <option value="{{ $y }}" {{ $tanggalSaatIni->year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button onclick="navigate(1)"
                class="group px-3 sm:px-4 py-2 text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center space-x-1 sm:space-x-2 text-sm sm:text-base">
                <span>Berikutnya</span>
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transform group-hover:translate-x-1 transition-transform duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 mb-4 text-xs text-gray-600 px-1">
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-sm bg-orange-100 border border-orange-300"></span>
                <span>Event biasa</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-sm bg-orange-200 border-l-2 border-orange-500"></span>
                <span>Event berulang tahunan</span>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
            <div class="grid grid-cols-7 bg-gradient-to-r from-orange-50 to-orange-100">
                @php
                    $fullDays  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $shortDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                @endphp

                @foreach ($fullDays as $index => $hari)
                    <div class="calendar-day-header py-2 sm:py-4 text-center text-sm font-semibold text-gray-700">
                        <span class="hidden sm:inline">{{ $hari }}</span>
                        <span class="sm:hidden">{{ $shortDays[$index] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-gray-200">
                @foreach ($kalender as $minggu)
                    @foreach ($minggu as $hari)
                        <div class="min-h-[100px] sm:min-h-[120px] bg-white relative group calendar-cell">
                            @if ($hari)
                                @php
                                    $dateKey = sprintf(
                                        '%s-%s-%s',
                                        $tanggalSaatIni->year,
                                        str_pad($tanggalSaatIni->month, 2, '0', STR_PAD_LEFT),
                                        str_pad($hari, 2, '0', STR_PAD_LEFT),
                                    );
                                    $isToday =
                                        $tanggalSaatIni->year == now()->year &&
                                        $tanggalSaatIni->month == now()->month &&
                                        $hari == now()->day;
                                @endphp

                                <button
                                    onclick="openEventModal('{{ $dateKey }}')"
                                    class="w-full h-full flex flex-col hover:bg-orange-50 rounded-lg p-1 sm:p-3 transition-colors duration-300 {{ $isToday ? 'bg-orange-50' : '' }}">

                                    <span class="text-xs sm:text-sm font-medium inline-flex items-center justify-center w-6 h-6 sm:w-8 sm:h-8
                                        {{ $isToday ? 'text-white bg-orange-500 rounded-full shadow-lg' : 'text-gray-700' }}">
                                        {{ $hari }}
                                    </span>

                                    <!-- Event List -->
                                    <div class="mt-1 sm:mt-2 space-y-1 max-h-16 sm:max-h-20 overflow-y-auto custom-scrollbar">
                                        @if (isset($events[$dateKey]))
                                            @foreach ($events[$dateKey] as $event)
                                                <div class="group/event relative">
                                                    <div
                                                        onclick="openEventModal('{{ $dateKey }}', { id: {{ $event->id }}, title: '{{ addslashes($event->title) }}', content: '{{ addslashes($event->content) }}' })"
                                                        class="event-title px-1.5 sm:px-2.5 py-1 sm:py-1.5 text-xs rounded-lg truncate cursor-pointer transition-colors duration-200 shadow-sm flex items-center gap-1
                                                            {{ $event->is_recurring
                                                                ? 'event-recurring text-orange-900'
                                                                : 'bg-orange-100 text-orange-800 hover:bg-orange-200' }}">

                                                        {{-- Ikon berulang --}}
                                                        @if ($event->is_recurring)
                                                            <svg class="w-2.5 h-2.5 flex-shrink-0 text-orange-600"
                                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                        @endif

                                                        <span class="truncate">{{ $event->title }}</span>
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
        <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white transform transition-all">
            <div class="mt-2">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 id="modalTitle" class="text-xl font-semibold text-gray-900">Tambah Event</h3>
                        <!-- Badge sumber event berulang -->
                        <span id="recurringBadge"
                            class="hidden mt-1 inline-flex items-center gap-1 text-xs text-orange-600 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Event berulang tahunan
                        </span>
                    </div>
                    <button onclick="closeEventModal()"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="eventForm" onsubmit="submitEvent(event)" class="space-y-5">
                    <input type="hidden" id="eventDate" name="date">
                    <input type="hidden" id="eventId" name="id">

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Event</label>
                        <input type="text" id="title" name="title" required
                            class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 transition-colors duration-200"
                            placeholder="Masukkan judul event">
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea id="content" name="content" rows="4"
                            class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring focus:ring-orange-200 transition-colors duration-200"
                            placeholder="Tambahkan deskripsi event"></textarea>
                        <div class="flex justify-end mt-1">
                            <span id="charCount" class="text-sm text-gray-500">0/255 karakter</span>
                        </div>
                    </div>

                    <!-- Toggle is_recurring -->
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-xl border border-orange-100">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-700">Ulangi setiap tahun</span>
                            <span class="text-xs text-gray-500 mt-0.5">
                                Event ini akan muncul otomatis di tahun-tahun berikutnya
                            </span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-3 flex-shrink-0">
                            <input type="checkbox" id="is_recurring" name="is_recurring"
                                class="sr-only peer" value="1">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2
                                peer-focus:ring-orange-300 rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                after:h-5 after:w-5 after:transition-all
                                peer-checked:bg-orange-500">
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-1">
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
        let currentEventId = null;

        // ─── Navigate ───────────────────────────────────────────────────────────────
        function navigate(arah) {
            const urlParams = new URLSearchParams(window.location.search);
            let tahun = parseInt(urlParams.get('tahun')) || {{ $tanggalSaatIni->year }};
            let bulan = parseInt(urlParams.get('bulan')) || {{ $tanggalSaatIni->month }};

            bulan += arah;
            if (bulan > 12) { bulan = 1; tahun++; }
            else if (bulan < 1) { bulan = 12; tahun--; }

            window.location.href = `?tahun=${tahun}&bulan=${bulan}`;
        }

        function updateKalender() {
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            window.location.href = `?tahun=${tahun}&bulan=${bulan}`;
        }

        // ─── Character counter ───────────────────────────────────────────────────────
        function initCharCounter() {
            const textarea  = document.getElementById('content');
            const charCount = document.getElementById('charCount');
            const maxLength = 255;

            function update() {
                const len = textarea.value.length;
                charCount.textContent = `${len}/${maxLength} karakter`;

                if (len >= maxLength) {
                    charCount.className = 'text-sm text-red-500 font-medium';
                    textarea.classList.add('border-red-500');
                } else if (len >= maxLength * 0.9) {
                    charCount.className = 'text-sm text-orange-500';
                    textarea.classList.remove('border-red-500');
                } else {
                    charCount.className = 'text-sm text-gray-500';
                    textarea.classList.remove('border-red-500');
                }
            }

            textarea.addEventListener('input', update);
            textarea.addEventListener('keydown', (e) => {
                if (textarea.value.length >= maxLength &&
                    !['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)) {
                    e.preventDefault();
                }
            });
            update();
        }

        // ─── Modal ───────────────────────────────────────────────────────────────────
        async function openEventModal(date, event = null) {
            const modal          = document.getElementById('eventModal');
            const modalTitle     = document.getElementById('modalTitle');
            const deleteButton   = document.getElementById('deleteButton');
            const recurringBadge = document.getElementById('recurringBadge');
            const form           = document.getElementById('eventForm');

            form.reset();
            document.getElementById('eventDate').value = date;
            document.getElementById('is_recurring').checked = false;
            recurringBadge.classList.add('hidden');

            if (event) {
                try {
                    const response = await fetch(`/admin/events/${event.id}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Gagal mengambil data event');

                    const data      = await response.json();
                    const eventData = data.data;

                    modalTitle.textContent = 'Edit Event';
                    document.getElementById('eventId').value   = eventData.id;
                    document.getElementById('title').value     = eventData.title;
                    document.getElementById('content').value   = eventData.content || '';

                    // Isi toggle is_recurring
                    const isRecurring = Boolean(eventData.is_recurring);
                    document.getElementById('is_recurring').checked = isRecurring;

                    // Tampilkan badge jika event ini berulang
                    if (isRecurring) {
                        recurringBadge.classList.remove('hidden');
                    }

                    deleteButton.classList.remove('hidden');
                    currentEventId = eventData.id;

                    initCharCounter();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Gagal memuat detail event. Silakan coba lagi.');
                    return;
                }
            } else {
                modalTitle.textContent = 'Tambah Event';
                document.getElementById('eventId').value = '';
                deleteButton.classList.add('hidden');
                currentEventId = null;
                initCharCounter();
            }

            modal.classList.remove('hidden');
        }

        function closeEventModal() {
            document.getElementById('eventModal').classList.add('hidden');
            document.getElementById('eventForm').reset();
            document.getElementById('recurringBadge').classList.add('hidden');
            currentEventId = null;
        }

        // ─── Submit ───────────────────────────────────────────────────────────────
        async function submitEvent(e) {
            e.preventDefault();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) { alert('CSRF token not found. Please refresh the page.'); return; }

            const formData   = new FormData(document.getElementById('eventForm'));
            const isEdit     = currentEventId !== null;
            const url        = isEdit ? `/admin/events/${currentEventId}` : '/admin/events';

            const jsonData   = {};
            formData.forEach((value, key) => { jsonData[key] = value; });

            // Checkbox tidak ikut FormData jika tidak dicentang, jadi force boolean
            jsonData.is_recurring = document.getElementById('is_recurring').checked ? 1 : 0;

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
                    const errorMessages = data.errors
                        ? Object.values(data.errors).flat().join('\n')
                        : (data.message || 'Terjadi kesalahan saat menyimpan event');
                    alert(errorMessages);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        }

        // ─── Delete ───────────────────────────────────────────────────────────────
        async function deleteEvent() {
            if (!currentEventId) return;
            if (!confirm('Apakah Anda yakin ingin menghapus event ini?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) { alert('CSRF token not found. Please refresh the page.'); return; }

            try {
                const response = await fetch(`/admin/events/${currentEventId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
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

        // ─── Keyboard ─────────────────────────────────────────────────────────────
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft')  navigate(-1);
            else if (e.key === 'ArrowRight') navigate(1);
            else if (e.key === 'Escape') closeEventModal();
        });
    </script>
@endsection