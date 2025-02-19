<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Server | E-Learning Budaya Melayu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-yellow-50 to-orange-100 min-h-screen">
    <div class="container mx-auto px-4 h-screen flex items-center justify-center">
        <div class="text-center">
            <div class="mb-8">
                <img src="/api/placeholder/400/300" alt="Ilustrasi Kesalahan Server" class="mx-auto mb-4" />
                <div class="inline-block border-4 border-orange-600 p-8 rounded-lg bg-white shadow-xl">
                    <h1 class="text-6xl font-bold text-orange-600 mb-4">500</h1>
                    <div class="h-1 w-32 bg-orange-600 mx-auto mb-6"></div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Maaf, Terjadi Kesalahan pada Server</h2>
                    <p class="text-gray-600 mb-6">
                        Ibarat perahu yang tertahan ombak,<br>
                        server kami sedang mengalami gangguan.<br>
                        Mohon tunggu sejenak atau kembali ke halaman utama.
                    </p>
                    <div class="space-y-4">
                        <a href="/"
                            class="inline-block px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-300 shadow-md">
                            Kembali ke Beranda
                        </a>
                        <p class="text-sm text-gray-500 mt-4">
                            Tim kami sedang bekerja untuk memperbaiki masalah ini
                        </p>
                        @if (config('app.debug'))
                            <div class="mt-4 p-4 bg-gray-100 rounded-lg">
                                <p class="text-left text-sm text-gray-600">
                                    Error Details (Debug Mode):<br>
                                    <span class="font-mono">{{ $exception->getMessage() }}</span>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Motif Melayu -->
            <div class="mt-8">
                <div class="flex justify-center space-x-4">
                    <span class="text-orange-600">&bull;</span>
                    <span class="text-orange-600">&bull;</span>
                    <span class="text-orange-600">&bull;</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hiasan Bawah -->
    <div class="fixed bottom-0 left-0 w-full">
        <div class="h-4 bg-orange-600"></div>
        <div class="h-2 bg-yellow-500"></div>
    </div>

    <!-- Animasi Loading (Optional) -->
    <div class="fixed top-4 right-4">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-orange-600 border-t-transparent"></div>
    </div>
</body>

</html>
