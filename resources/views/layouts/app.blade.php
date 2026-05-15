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
    <body class="i3p-body antialiased">
        <div class="i3p-shell">
            @include('layouts.navigation')

            <div class="i3p-main lg:pl-[20rem]">
                @isset($header)
                    <header class="pt-6 lg:pt-8">
                        <div class="i3p-container">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="pb-12">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
