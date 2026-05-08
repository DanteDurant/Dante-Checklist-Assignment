<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Checklist') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
<div class="min-h-screen overflow-x-hidden">
    <x-app.nav />

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <x-ui.flash type="success" :message="session('status')" />
        <x-ui.flash type="error" :message="session('error')" />

        @if ($errors->any())
            <x-ui.flash type="error" message="Please review the highlighted fields and try again." />
        @endif

        {{ $slot }}
    </main>
</div>
</body>
</html>

