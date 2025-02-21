<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="icon" type="image/x-icon" href="/assets/Logo emodul melayu2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>

    <script>
        window.onload = () => lucide.createIcons();
    </script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar - Fixed on large screens -->
        <div class="hidden lg:block fixed inset-y-0 left-0 w-64">
            @include('components.sidebar')
        </div>

        <!-- Mobile Sidebar -->
        <div class="lg:hidden">
            @include('components.sidebar')
        </div>

        <!-- Main Content - With offset for sidebar -->
        <main class="flex-1 lg:ml-64">
            <div class="p-8">
                @include('components.topbar')
                @yield('content')
                @stack('scripts')
            </div>
        </main>
    </div>
</body>

</html>
