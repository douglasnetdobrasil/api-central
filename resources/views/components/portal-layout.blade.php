<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portal do Cliente - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Carrega CSS e o app.js (que inicializa Alpine) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ========================================================== --}}
    {{-- ||||||||||||||||||| 1. JQUERY ADICIONADO ||||||||||||||||||| --}}
    {{-- ========================================================== --}}
    {{-- Adicionamos jQuery via CDN ANTES do @stack --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        {{-- Navegação (código existente) --}}
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            {{-- ... (seu código da nav) ... --}}
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('portal.dashboard') }}">
                                <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                            </a>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                           <h2 class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-900 dark:text-gray-100">
                                Portal do Cliente
                           </h2>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <a href="{{ route('portal.logout') }}"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                Sair
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Conteúdo da Página (código existente) --}}
        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- ========================================================== --}}
    {{-- ||||||||||||||||||| 2. @STACK ADICIONADO ||||||||||||||||||| --}}
    {{-- ========================================================== --}}
    {{-- Renderiza os scripts empurrados pelas views filhas (como o create.blade.php) --}}
    @stack('scripts')

</body>
</html>