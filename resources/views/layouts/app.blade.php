<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Carrega CSS/JS definidos no vite.config.js (inclui Alpine.js) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        {{-- Permite que as views filhas adicionem CSS específico --}}
        @stack('head') 

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>

        @livewireScripts

        {{-- Toast Notification (sem alterações) --}}
        <div 
            x-data="{ show: false, message: '', type: 'success' }" 
            @show-toast.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; setTimeout(() => show = false, 3000)"
            x-show="show" 
            x-transition
            class="fixed bottom-5 right-5 z-50 px-4 py-2 rounded-md text-white font-semibold"
            :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }"
            style="display: none;"
        >
            <span x-text="message"></span>
        </div>
        <script> console.log("LOG DO LAYOUT: Antes do @stack('scripts')"); </script>
        @stack('scripts')
        <script> console.log("LOG DO LAYOUT: Depois do @stack('scripts')"); </script>
        {{-- ========================================================== --}}
        {{-- ||||||||||||||||||| AQUI ESTÁ A CORREÇÃO ||||||||||||||||||| --}}
        {{-- ========================================================== --}}
        {{-- Imprime qualquer script que foi "empurrado" pelas views filhas --}}
      

    </body>
</html>