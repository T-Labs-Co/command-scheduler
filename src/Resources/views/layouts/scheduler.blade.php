<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ (!empty($title) ? $title . ' ' : '') . '⏰' }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/scheduler.css', 'resources/js/scheduler.js'])

    {{-- Flatpickr  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @stack('head')
    @yield('head')
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">
    {{-- NAVBAR mobile only --}}
    <x-mary-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="font-bold">{{ 'Command Scheduler ⏰' }}</div>
        </x-slot:brand>
        <x-slot:actions class="flex items-center">
            <x-mary-button icon="o-magnifying-glass" class="btn-ghost btn-circle" @click="$dispatch('mary-search-open')" />
            <label for="main-drawer" class="lg:hidden ml-2">
                <x-mary-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    {{-- MAIN --}}
    <x-mary-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit" :collapsed="true">

            {{-- BRAND --}}
            <div class="ml-5 pt-5 font-bold">{{ 'COMMAND SCHEDULER' }}</div>

            {{-- MENU --}}
            <x-mary-menu activate-by-route>
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('admin.scheduler.dashboard') }}" />
                <x-mary-menu-item title="Tasks" icon="o-list-bullet" link="{{ route('admin.scheduler.tasks') }}" />
                <x-mary-menu-item title="Recent Runs" icon="o-clock" link="{{ route('admin.scheduler.runs') }}" />

                {{-- User --}}
                @if($user = auth()->user())
                    <x-mary-menu-separator />

                    <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-mary-list-item>
                @endif

            </x-mary-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    {{-- Toast --}}
    <x-mary-toast />

    @stack('footer')
    @yield('footer')
    @stack('scripts')

</body>
</html>
