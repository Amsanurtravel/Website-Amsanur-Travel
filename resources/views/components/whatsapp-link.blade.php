@props(['package' => null, 'source' => null])

@php
    // The page the button is on, for the click counts in the admin panel.
    $source ??= match (true) {
        request()->routeIs('home') => 'beranda',
        request()->routeIs('umrah.*') => 'umroh',
        request()->routeIs('haji.*') => 'haji-khusus',
        request()->routeIs('packages.*') => $package ? 'paket' : 'paket-umum',
        request()->routeIs('legal') => 'legalitas',
        request()->routeIs('about') => 'tentang-kami',
        request()->routeIs('representatives') => 'perwakilan',
        request()->routeIs('mentors') => 'pembimbing',
        request()->routeIs('articles.*') => 'artikel',
        default => 'lainnya',
    };

    $number = \App\Support\WhatsApp::number($package?->whatsapp ?: config('site.whatsapp'));
@endphp

@if ($number !== '')
    <a href="{{ route('whatsapp', array_filter(['dari' => $source, 'paket' => $package?->slug])) }}" target="_blank" rel="nofollow noopener" {{ $attributes }}>{{ $slot }}</a>
@endif
