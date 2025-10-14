@php
    $pageTitle = $title ?? null;
@endphp

<x-ksu-layout :page-title="$pageTitle">
    {{ $slot ?? '' }}
    @yield('content')
</x-ksu-layout>
