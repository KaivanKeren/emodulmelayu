<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | E-Learning Budaya Melayu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-yellow-50 to-orange-100 min-h-screen">
    <div class="container mx-auto px-4 h-screen flex items-center justify-center">
        <div class="text-center">
            <!-- Ilustrasi Khas Melayu -->
            <div class="mb-8">
                <div class="inline-block border-4 border-orange-600 p-8 rounded-lg bg-white shadow-xl">
                    <h1 class="text-6xl font-bold text-orange-600 mb-4">404</h1>
                    <div class="h-1 w-32 bg-orange-600 mx-auto mb-6"></div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Mohon Maaf, Halaman Tidak Ditemukan</h2>
                    <p class="text-gray-600 mb-8">
                        Seperti mencari mutiara di lautan Melayu,<br>
                        halaman yang Anda cari belum dapat ditemukan.
                    </p>
                    <!-- Tombol dengan motif Melayu -->
                    <div class="space-y-4">
                        @if (auth()->user()->role === 'Admin')
                            <a href="/admin/dashboard"
                                class="inline-block px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-300 shadow-md">
                                Kembali ke Dashboard
                            </a>
                        @elseif (auth()->user()->role === 'Guru')
                            <a href="/guru/assessments"
                                class="inline-block px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-300 shadow-md">
                                Kembali ke Penilaian
                            </a>
                        @endif
                        <p class="text-sm text-gray-500 mt-4">
                            Atau coba telusuri koleksi pembelajaran budaya Melayu kami
                        </p>
                    </div>
                </div>
            </div>
            <!-- Motif Melayu Footer -->
            <div class="mt-8">
                <div class="flex justify-center space-x-4">
                    <span class="text-orange-600">&bull;</span>
                    <span class="text-orange-600">&bull;</span>
                    <span class="text-orange-600">&bull;</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sentuhan Tambahan Motif Melayu -->
    <div class="fixed bottom-0 left-0 w-full">
        <div class="h-4 bg-orange-600"></div>
        <div class="h-2 bg-yellow-500"></div>
    </div>
</body>

</html>
