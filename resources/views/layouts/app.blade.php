<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', $title ?? 'SandLab')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Head (CSS utama, meta, dll) --}}
    @include('components.head')

    {{-- Styles yang dipush dari halaman (MUSTI di HEAD) --}}
    @stack('styles')
</head>

<body data-sidebar="dark">
    <div id="layout-wrapper">
        {{-- Topbar --}}
        @include('components.topbar')

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Content --}}
        <div class="main-content">
            @yield('content')
        </div>

        {{-- Rightbar --}}
        @include('components.rightbar')

        {{-- Overlay --}}
        <div class="rightbar-overlay"></div>

        {{-- Footer (kalau footer HTML, bukan scripts) --}}
        @include('components.footer')
    </div>

    {{-- Scripts (JS utama) --}}
    @include('components.scripts')

    {{-- Scripts yang dipush dari halaman (di akhir BODY) --}}
    @stack('scripts')
</body>
</html>
