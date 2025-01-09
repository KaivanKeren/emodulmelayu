@extends('layouts.admin')

@section('title', 'Kalender')

@section('content')
    <div class="w-full max-w-4xl mx-auto bg-white p-8 shadow-xl rounded-xl">
        <!-- Bagian Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 space-y-4 sm:space-y-0">
            <button onclick="navigate(-1)"
                class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Sebelumnya</span>
            </button>

            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">
                    {{ $tanggalSaatIni->locale('id')->translatedFormat('F Y') }}
                </h1>
                <div class="flex space-x-3">
                    <select id="bulan"
                        class="form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-gray-200 text-gray-700"
                        onchange="updateKalender()">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $tanggalSaatIni->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select id="tahun"
                        class="form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-gray-200 text-gray-700"
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
                class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition-colors duration-200 flex items-center space-x-2">
                <span>Berikutnya</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Grid Kalender -->
        <div class="bg-white rounded-xl overflow-hidden">
            <!-- Header Hari -->
            <div class="grid grid-cols-7 gap-px bg-gray-100 border-b border-gray-200">
                @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                    <div class="py-3 text-center text-sm font-semibold text-gray-600">
                        {{ $hari }}
                    </div>
                @endforeach
            </div>

            <!-- Hari Kalender -->
            <div class="grid grid-cols-7 gap-px bg-gray-200">
                @foreach ($kalender as $minggu)
                    @foreach ($minggu as $hari)
                        <div class="min-h-[80px] bg-white p-2 relative group">
                            @if ($hari)
                                <button
                                    class="w-full h-full flex flex-col hover:bg-orange-300/20 rounded-lg p-2 transition-colors duration-200
                                    {{ $tanggalSaatIni->year == now()->year && $tanggalSaatIni->month == now()->month && $hari == now()->day
                                        ? 'bg-orange-600/30'
                                        : '' }}">
                                    <span
                                        class="text-sm font-medium {{ $tanggalSaatIni->year == now()->year && $tanggalSaatIni->month == now()->month && $hari == now()->day
                                            ? 'text-orange-700'
                                            : 'text-gray-800' }}">
                                        {{ $hari }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                @endforeach
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

        // Navigasi keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                navigate(-1);
            } else if (e.key === 'ArrowRight') {
                navigate(1);
            }
        });
    </script>
@endsection
