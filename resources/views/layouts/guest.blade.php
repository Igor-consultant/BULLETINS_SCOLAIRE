<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
            <div class="mb-6 text-center">
                <a href="/">
                    <x-application-logo class="mx-auto h-28 w-auto rounded-2xl border border-white/70 bg-white/70 p-2 shadow-[0_18px_60px_rgba(15,23,42,0.12)]" />
                </a>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#b02f25]">Institut Polytechnique Pierre Prie</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">BULLETINS SCOLAIRE</h1>
                    <p class="mt-2 text-sm text-slate-600">Acces securise a la plateforme interne de gestion des bulletins I3P.</p>
                </div>
            </div>

            <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white/85 px-6 py-6 shadow-[0_18px_60px_rgba(15,23,42,0.12)] backdrop-blur">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
