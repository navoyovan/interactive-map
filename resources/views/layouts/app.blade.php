<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Interactive Map') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

            <!-- Logo Web -->
            <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">

        <!-- Scripts @vite(['resources/css/app.css', 'resources/js/app.js'])-->
        
        <script src="/offlined/tailwind.js"></script>
    <!-- alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.user = @json(Auth::user());
    </script>



    </head>

        <body class="font-sans antialiased">
            <div class="min-h-screen bg-transparent pt-16"> {{-- this --}}
                @include('layouts.navigation')

                <!-- Optional Page Heading -->
                @isset($header)
                    <header class="bg-transparent">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    @yield('content')
                </main>
            </div>
        </body>

</html>
