@php
    $site = config('site');
@endphp

<x-layouts.site
    title="Perwakilan Daerah"
    :description="'Daftar perwakilan resmi '.$site['brand'].' ('.$site['legal_name'].') di '.($regions->isNotEmpty() ? $regions->keys()->implode(', ') : 'daerah').'. Pembayaran tetap hanya ke rekening atas nama perusahaan.'"
    :canonical="route('representatives')"
    :schema="$schema"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-4xl px-4 py-8 md:py-12">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" />
            <h1 class="mt-3 font-serif text-4xl font-bold text-brand-dark">Perwakilan Daerah</h1>
            <p class="mt-3 max-w-2xl leading-relaxed text-slate-700">Selain lewat kantor pusat di Jakarta, Anda dapat berkonsultasi dan mendaftar umroh atau haji khusus lewat perwakilan resmi kami di daerah berikut.</p>

            @if ($regions->count() > 1)
                <nav aria-label="Wilayah" class="mt-6">
                    <ul class="flex flex-wrap gap-2 text-sm font-semibold">
                        @foreach ($regions->keys() as $region)
                            <li><a href="#wilayah-{{ Str::slug($region) }}" class="inline-flex min-h-11 items-center rounded-full border border-gold/50 bg-white px-4 text-brand-dark hover:bg-gold-light/30">{{ $region }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-4xl space-y-10 px-4 py-10">
        <section aria-labelledby="pembayaran" class="rounded-2xl border-2 border-gold/50 p-6">
            <h2 id="pembayaran" class="flex items-center gap-2 font-serif text-xl font-bold text-brand-dark"><x-site-icon name="shield-check" class="size-6 shrink-0 text-brand" /> Sebelum membayar</h2>
            <ul class="mt-3 space-y-2 leading-relaxed text-slate-700">
                <li>Pembayaran hanya ke <a href="{{ route('legal') }}#rekening" class="font-semibold text-brand underline underline-offset-2">rekening atas nama {{ $site['legal_name'] }}</a>, termasuk bila Anda mendaftar lewat perwakilan. Jangan transfer ke rekening pribadi.</li>
                <li>Ragu apakah seseorang benar perwakilan kami? Tanyakan dulu ke kantor pusat.</li>
            </ul>
            <x-whatsapp-link class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-full bg-wa px-5 font-semibold text-white hover:bg-wa-dark">
                <x-site-icon name="whatsapp" /> Tanya kantor pusat
            </x-whatsapp-link>
        </section>

        @forelse ($regions as $region => $representatives)
            <section id="wilayah-{{ Str::slug($region) }}" aria-labelledby="wilayah-{{ Str::slug($region) }}-judul" class="scroll-mt-6">
                <h2 id="wilayah-{{ Str::slug($region) }}-judul" class="font-serif text-2xl font-bold text-brand-dark">{{ $region }}</h2>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($representatives as $representative)
                        <li class="rounded-2xl border border-slate-200 p-5">
                            <p class="font-semibold text-brand-dark">{{ $representative->name }}</p>
                            @if ($representative->address)
                                <p class="mt-2 flex gap-2 text-sm leading-relaxed text-slate-600"><x-site-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-gold-dark" /> {{ $representative->address }}</p>
                            @endif
                            @if ($representative->phone)
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $representative->phone) }}" class="mt-1 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-brand hover:underline"><x-site-icon name="phone" class="size-4" /> {{ $representative->phone }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="rounded-2xl bg-cream p-6 text-slate-700">Daftar perwakilan daerah sedang kami perbarui. Untuk sementara, silakan hubungi kantor pusat.</p>
        @endforelse

        <section aria-labelledby="kantor-pusat" class="rounded-3xl bg-pattern p-6 text-white md:p-8">
            <h2 id="kantor-pusat" class="font-serif text-2xl font-bold">{{ $site['office']['name'] }}</h2>
            @if ($site['office']['address'])
                <p class="mt-3 flex gap-2 leading-relaxed text-slate-200"><x-site-icon name="map-pin" class="mt-0.5 shrink-0 text-gold-light" /> {{ $site['office']['address'] }}</p>
            @endif
            @if ($site['office']['hours'])
                <div class="mt-3 flex gap-2 text-sm text-slate-200"><x-site-icon name="clock" class="mt-0.5 size-4 shrink-0 text-gold-light" /> <x-office-hours /></div>
            @endif
            <a href="{{ route('about') }}#kontak" class="mt-5 inline-flex min-h-11 items-center gap-1 font-semibold text-gold-light underline underline-offset-2">Semua kontak kantor pusat <x-site-icon name="arrow-right" class="size-4" /></a>
        </section>
    </div>
</x-layouts.site>
