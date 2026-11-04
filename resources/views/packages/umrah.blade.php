@php
    // Filter links keep the other filters; the default sort is left out of the URL.
    $link = fn (array $change) => route('umrah.index', array_filter(
        array_merge($filters, $change),
        fn ($value, $key) => $value !== null && ! ($key === 'urut' && $value === 'tanggal'),
        ARRAY_FILTER_USE_BOTH,
    ));

    $chip = 'inline-flex min-h-11 items-center whitespace-nowrap rounded-full border px-4 text-sm font-semibold';
    $on = 'border-brand bg-brand text-white';
    $off = 'border-slate-300 bg-white text-slate-700 hover:border-brand';
    $filtered = $filters['bulan'] || $filters['bintang'];
@endphp

<x-layouts.site
    title="Jadwal Paket Umroh"
    :description="'Jadwal umroh '.config('site.brand').', berizin '.collect(config('site.permits'))->firstWhere('code', 'PPIU')['short'].'. Harga per tipe kamar, hotel Makkah dan Madinah, maskapai, dan rincian paket.'"
    :canonical="route('umrah.index', array_filter(['bulan' => $filters['bulan'], 'bintang' => $filters['bintang']]))"
    :noindex="$filtered"
    :schema="$schema"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-6xl px-4 py-8 md:py-12">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" />
            <h1 class="mt-3 font-serif text-4xl font-bold text-brand-dark">Jadwal Umroh</h1>
            <p class="mt-3 max-w-2xl leading-relaxed text-slate-700">Semua keberangkatan umroh yang masih bisa dipilih. Harga per orang sesuai tipe kamar; rincian yang termasuk dan tidak termasuk ada di setiap paket.</p>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
        @if ($total > 0)
            <div class="space-y-4">
                @if ($months->count() > 1)
                    <nav aria-label="Filter bulan keberangkatan" class="flex flex-wrap gap-2">
                        <a href="{{ $link(['bulan' => null]) }}" @class([$chip, $on => ! $filters['bulan'], $off => (bool) $filters['bulan']]) @if (! $filters['bulan']) aria-current="true" @endif>Semua bulan</a>
                        @foreach ($months as $value => $label)
                            <a href="{{ $link(['bulan' => $value]) }}" @class([$chip, $on => $filters['bulan'] === $value, $off => $filters['bulan'] !== $value]) @if ($filters['bulan'] === $value) aria-current="true" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>
                @endif

                @if ($stars->isNotEmpty())
                    <nav aria-label="Filter bintang hotel Makkah" class="flex flex-wrap gap-2">
                        <a href="{{ $link(['bintang' => null]) }}" @class([$chip, $on => ! $filters['bintang'], $off => (bool) $filters['bintang']]) @if (! $filters['bintang']) aria-current="true" @endif>Semua hotel</a>
                        @foreach ($stars as $value)
                            <a href="{{ $link(['bintang' => $value]) }}" @class([$chip, $on => $filters['bintang'] === $value, $off => $filters['bintang'] !== $value]) @if ($filters['bintang'] === $value) aria-current="true" @endif>Hotel bintang {{ $value }}</a>
                        @endforeach
                    </nav>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-600" role="status">{{ $packages->count() }} dari {{ $total }} jadwal</p>
                    <nav aria-label="Urutkan" class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-slate-600">Urutkan:</span>
                        <a href="{{ $link(['urut' => 'tanggal']) }}" @class([$chip, $on => $filters['urut'] === 'tanggal', $off => $filters['urut'] !== 'tanggal']) @if ($filters['urut'] === 'tanggal') aria-current="true" @endif>Tanggal terdekat</a>
                        <a href="{{ $link(['urut' => 'harga']) }}" @class([$chip, $on => $filters['urut'] === 'harga', $off => $filters['urut'] !== 'harga']) @if ($filters['urut'] === 'harga') aria-current="true" @endif>Harga termurah</a>
                    </nav>
                </div>
            </div>
        @endif

        @if ($packages->isEmpty())
            <div class="mt-8 rounded-2xl bg-cream p-6 text-slate-700">
                @if ($filtered)
                    <p>Tidak ada jadwal yang cocok dengan pilihan ini.</p>
                    <a href="{{ route('umrah.index') }}" class="mt-3 inline-flex items-center gap-1 py-2 font-semibold text-brand hover:underline">Tampilkan semua jadwal <x-site-icon name="arrow-right" class="size-4" /></a>
                @else
                    <p>Jadwal umroh berikutnya sedang disiapkan. Tanyakan jadwal terbaru lewat WhatsApp.</p>
                    <x-whatsapp-link class="mt-4 inline-flex min-h-12 items-center gap-2 rounded-full bg-wa px-6 font-semibold text-white hover:bg-wa-dark"><x-site-icon name="whatsapp" /> Tanya jadwal</x-whatsapp-link>
                @endif
            </div>
        @else
            <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    <li><x-package-card :package="$package" heading-level="h2" /></li>
                @endforeach
            </ul>
        @endif

        <aside class="mt-12 grid gap-4 rounded-2xl border border-slate-200 p-6 md:grid-cols-[auto_1fr] md:items-start">
            <x-site-icon name="info" class="size-7 text-brand" />
            <div class="text-sm leading-relaxed text-slate-700">
                <p>Harga dapat berubah sewaktu-waktu mengikuti kebijakan Kerajaan Arab Saudi dan maskapai; harga yang berlaku dikonfirmasi saat mendaftar. Pembayaran hanya ke rekening atas nama {{ config('site.legal_name') }}.</p>
                <a href="{{ route('legal') }}" class="mt-2 inline-flex min-h-11 items-center gap-1 font-semibold text-brand hover:underline">Lihat legalitas dan rekening resmi <x-site-icon name="arrow-right" class="size-4" /></a>
            </div>
        </aside>
    </div>
</x-layouts.site>
