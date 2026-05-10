<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Compliance Management System') }}</title>

    <script>
        // Prevent theme flash: apply theme before CSS loads.
        (function () {
            try {
                const stored = localStorage.getItem('theme'); // 'light' | 'dark' | null
                const theme = stored || 'light';
                const root = document.documentElement;
                root.classList.toggle('dark', theme === 'dark');
                root.style.colorScheme = theme === 'dark' ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden">
<div class="min-h-screen overflow-x-hidden">
    <x-app.nav />

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <x-ui.flash type="success" :message="session('status')" />
        <x-ui.flash type="error" :message="session('error')" />

        @if ($errors->any())
            <x-ui.flash type="error" :message="$errors->first() ?: 'Check the highlighted fields and try again.'" />
        @endif

        {{ $slot }}
    </main>
</div>

<div id="ui-toast-root" class="pointer-events-none fixed bottom-4 right-4 z-50 flex max-w-sm flex-col gap-2 p-2 sm:bottom-6 sm:right-6" aria-live="polite"></div>
</body>
</html>
