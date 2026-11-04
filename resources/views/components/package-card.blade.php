@props(['package', 'headingLevel' => 'h3'])

@php
    $lowest = $package->prices()[0] ?? null;
@endphp

<article class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-gold hover:shadow-md has-[a:focus-visible]:border-gold has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-brand has-[a:focus-visible]:ring-offset-2">
    <div class="flex items-center justify-between gap-3 bg-cream px-5 py-3">
        <p class="flex items-center gap-2 text-sm font-semibold text-gold-dark">
            <x-site-icon name="calendar" class="size-4" />
            @if ($package->departure_date)
                <time datetime="{{ $package->departure_date->toDateString() }}">{{ $package->departure_date->translatedFormat('j F Y') }}</time>
            @else
                {{ $package->departure_note ?? 'Jadwal menyusul' }}
            @endif
        </p>
        @if ($package->seats_left === 0)
            <span class="rounded-full bg-slate-700 px-2.5 py-1 text-xs font-bold text-white">Penuh</span>
        @elseif ($package->seats_left !== null)
            <span class="rounded-full bg-brand-red px-2.5 py-1 text-xs font-bold text-white">Sisa {{ $package->seats_left }} kursi</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        @if ($package->program_label)
            <p class="text-xs font-bold tracking-wide text-brand uppercase">{{ $package->program_label }}</p>
        @endif

        <{{ $headingLevel }} class="mt-1 font-serif text-xl leading-snug font-bold text-brand-dark">
            {{-- The whole card is the link; the card ring shows keyboard focus. The date tells same-named departures apart for screen readers. --}}
            <a href="{{ route('packages.show', $package) }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $package->name }}@if ($package->departureLabel())<span class="sr-only">, berangkat {{ $package->departureLabel() }}</span>@endif</a>
        </{{ $headingLevel }}>

        <dl class="mt-4 space-y-2 text-sm text-slate-700">
            @if ($package->durationLabel())
                <div class="flex items-center gap-2">
                    <dt class="sr-only">Durasi</dt>
                    <x-site-icon name="clock" class="size-4 text-slate-400" />
                    <dd>{{ $package->durationLabel() }}</dd>
                </div>
            @endif
            @if ($package->hotel_makkah)
                <div class="flex items-start gap-2">
                    <dt class="sr-only">Hotel Makkah</dt>
                    <x-site-icon name="building" class="mt-0.5 size-4 text-slate-400" />
                    <dd>Makkah <x-stars :count="$package->hotel_makkah_stars" /> {{ $package->hotel_makkah }}</dd>
                </div>
            @endif
            @if ($package->hotel_madinah)
                <div class="flex items-start gap-2">
                    <dt class="sr-only">Hotel Madinah</dt>
                    <x-site-icon name="building" class="mt-0.5 size-4 text-slate-400" />
                    <dd>Madinah <x-stars :count="$package->hotel_madinah_stars" /> {{ $package->hotel_madinah }}</dd>
                </div>
            @endif
            @if ($package->airline)
                <div class="flex items-center gap-2">
                    <dt class="sr-only">Maskapai</dt>
                    <x-site-icon name="plane" class="size-4 text-slate-400" />
                    <dd>{{ $package->airline }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
            @if ($lowest)
                <p>
                    <span class="block text-xs text-slate-500">Mulai</span>
                    <span class="block text-xl font-bold text-brand-dark">{{ $package->money($lowest['amount']) }}</span>
                    <span class="block text-xs text-slate-500">per orang, {{ Str::lower($lowest['note']) }}</span>
                </p>
            @else
                <p class="text-sm text-slate-600">Harga: tanyakan ke tim kami</p>
            @endif
            <span class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand" aria-hidden="true">Detail <x-site-icon name="arrow-right" class="size-4" /></span>
        </div>
    </div>
</article>
