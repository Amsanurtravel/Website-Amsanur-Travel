@php
    $prices = $package->prices();
    $lowest = $prices[0] ?? null;
    $isHaji = $package->type === \App\Models\Package::HAJI_KHUSUS;
    $permit = collect(config('site.permits'))->firstWhere('code', $isHaji ? 'PIHK' : 'PPIU');
    $departed = $package->hasDeparted();
    $details = array_filter([
        'Keberangkatan' => $package->departureLabel(),
        'Durasi' => $package->durationLabel(),
        'Maskapai' => $package->airline,
        'Rute berangkat' => $package->route_departure,
        'Rute pulang' => $package->route_return,
    ]);
@endphp

<x-layouts.site
    :title="$package->metaTitle()"
    :description="$package->metaDescription()"
    :canonical="route('packages.show', $package)"
    :noindex="$departed"
    :schema="$schema"
    :floating-whatsapp="false"
    :package="$departed ? null : $package"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-6xl px-4 py-8 md:py-10">
            <x-breadcrumbs :trail="[['Beranda', route('home')], $listing]" />

            <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold tracking-wide uppercase">
                <span class="rounded-full bg-brand px-3 py-1 text-white">{{ $package->typeLabel() }}</span>
                @if ($package->program_label)
                    <span class="rounded-full bg-gold-light px-3 py-1 text-brand-dark">{{ $package->program_label }}</span>
                @endif
            </div>

            <h1 class="mt-3 font-serif text-3xl leading-tight font-bold text-brand-dark md:text-4xl">{{ $package->name }}</h1>

            <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-lg text-slate-700">
                @if ($package->departureLabel())
                    <span class="inline-flex items-center gap-2"><x-site-icon name="calendar" class="text-gold-dark" />
                        @if ($package->departure_date)
                            <time datetime="{{ $package->departure_date->toDateString() }}">{{ $package->departureLabel() }}</time>
                        @else
                            {{ $package->departureLabel() }}
                        @endif
                    </span>
                @endif
                @if ($package->durationLabel())
                    <span class="inline-flex items-center gap-2"><x-site-icon name="clock" class="text-gold-dark" /> {{ $package->durationLabel() }}</span>
                @endif
            </p>

            @if ($permit)
                <p class="mt-4 flex items-start gap-2 text-sm text-slate-700">
                    <x-site-icon name="shield-check" class="mt-0.5 size-4 text-brand" />
                    <span>Diselenggarakan {{ config('site.legal_name') }}, berizin {{ $permit['code'] }} Nomor {{ $permit['number'] }}.</span>
                </p>
            @endif
        </div>
    </div>

    @if ($departed)
        <div class="mx-auto max-w-6xl px-4 pt-6">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-brand-red/30 bg-red-50 p-5">
                <p class="flex items-center gap-2 font-semibold text-brand-dark"><x-site-icon name="info" class="text-brand-red" /> Keberangkatan ini sudah lewat.</p>
                <a href="{{ $listing[1] }}" class="inline-flex items-center gap-1 py-2 font-semibold text-brand hover:underline">Lihat jadwal terbaru <x-site-icon name="arrow-right" class="size-4" /></a>
            </div>
        </div>
    @endif

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-8 lg:grid-cols-[1fr_22rem] lg:items-start">
        <div class="min-w-0 space-y-10">
            @if ($package->summary)
                <p class="text-lg leading-relaxed text-slate-700">{{ $package->summary }}</p>
            @endif

            {{-- Price per room type --}}
            <section aria-labelledby="harga">
                <h2 id="harga" class="font-serif text-2xl font-bold text-brand-dark">Harga per orang</h2>
                @if ($prices)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="w-full text-left">
                            <caption class="sr-only">Harga per orang menurut tipe kamar</caption>
                            <thead class="bg-slate-50 text-sm text-slate-600">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Tipe kamar</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold">Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($prices as $price)
                                    <tr>
                                        <th scope="row" class="px-4 py-4 font-normal">
                                            <span class="block font-semibold text-brand-dark">{{ $price['label'] }}</span>
                                            <span class="block text-sm text-slate-600">{{ $price['note'] }}</span>
                                        </th>
                                        <td class="px-4 py-4 text-right text-lg font-bold whitespace-nowrap text-brand-dark">{{ $package->money($price['amount']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-3 text-slate-700">Harga untuk keberangkatan ini dikonfirmasi langsung oleh tim kami.</p>
                @endif

                @if ($package->extraCosts())
                    <div class="mt-4 rounded-2xl bg-cream p-4">
                        <p class="text-sm font-semibold text-gold-dark">Biaya di luar harga paket</p>
                        <ul class="mt-2 space-y-1 text-slate-700">
                            @foreach ($package->extraCosts() as $cost)
                                <li class="flex flex-wrap justify-between gap-x-4"><span>{{ $cost['label'] }}</span>@if ($cost['amount'] !== null)<span class="font-semibold">{{ $package->money($cost['amount']) }}</span>@endif</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($package->notes)
                    <p class="mt-4 flex gap-2 text-sm leading-relaxed text-slate-600"><x-site-icon name="info" class="mt-0.5 size-4 text-slate-400" /> <span>{{ $package->notes }}</span></p>
                @endif

                <p class="mt-3 text-sm text-slate-500">
                    Info paket diperbarui <time datetime="{{ $package->updated_at->toDateString() }}">{{ $package->updated_at->translatedFormat('j F Y') }}</time>.
                    {{ $isHaji ? 'Harga mengikuti tahun keberangkatan sesuai nomor porsi.' : 'Harga yang berlaku dikonfirmasi saat mendaftar.' }}
                </p>
            </section>

            {{-- Trip details --}}
            <section aria-labelledby="rincian">
                <h2 id="rincian" class="font-serif text-2xl font-bold text-brand-dark">Rincian perjalanan</h2>
                <dl class="mt-4 divide-y divide-slate-200 rounded-2xl border border-slate-200">
                    @foreach ($details as $label => $value)
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between sm:gap-6">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="font-semibold text-slate-800 sm:text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                    @foreach (['Makkah' => [$package->hotel_makkah, $package->hotel_makkah_stars], 'Madinah' => [$package->hotel_madinah, $package->hotel_madinah_stars]] as $city => [$hotel, $hotelStars])
                        @if ($hotel)
                            <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between sm:gap-6">
                                <dt class="text-slate-500">Hotel {{ $city }}</dt>
                                <dd class="font-semibold text-slate-800 sm:text-right">{{ $hotel }} <x-stars :count="$hotelStars" /></dd>
                            </div>
                        @endif
                    @endforeach
                    @if ($package->accommodation_note)
                        <div class="flex flex-col gap-1 p-4 sm:flex-row sm:justify-between sm:gap-6">
                            <dt class="text-slate-500">Akomodasi lain</dt>
                            <dd class="font-semibold text-slate-800 sm:text-right">{{ $package->accommodation_note }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if ($itinerary = $package->itineraryRows())
                @php($hasDates = collect($itinerary)->contains(fn ($row) => $row['date']))
                <section aria-labelledby="jadwal-perjalanan">
                    <h2 id="jadwal-perjalanan" class="font-serif text-2xl font-bold text-brand-dark">{{ $isHaji ? 'Perkiraan jadwal perjalanan' : 'Jadwal perjalanan' }}</h2>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-cream text-brand-dark">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Hari ke</th>
                                    @if ($hasDates)
                                        <th scope="col" class="px-4 py-3 font-semibold">Tanggal</th>
                                    @endif
                                    <th scope="col" class="px-4 py-3 font-semibold">Kegiatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($itinerary as $row)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular-nums">{{ $row['days'] }}</td>
                                        @if ($hasDates)
                                            <td class="px-4 py-3 text-slate-600">{{ $row['date'] }}</td>
                                        @endif
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['activity'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">Jadwal sewaktu-waktu dapat berubah tanpa mengurangi nilai ibadah.</p>
                </section>
            @endif

            @if ($package->listOf('highlights'))
                <section aria-labelledby="keunggulan">
                    <h2 id="keunggulan" class="font-serif text-2xl font-bold text-brand-dark">Keunggulan program</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($package->listOf('highlights') as $item)
                            <li class="flex gap-3 rounded-xl bg-cream p-4 text-slate-800"><x-site-icon name="sparkles" class="mt-0.5 text-gold-dark" /> {{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($package->listOf('includes') || $package->listOf('excludes'))
                <section aria-labelledby="fasilitas" class="grid gap-6 md:grid-cols-2">
                    <h2 id="fasilitas" class="sr-only">Yang termasuk dan tidak termasuk</h2>
                    @if ($package->listOf('includes'))
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-semibold text-brand-dark">Sudah termasuk</h3>
                            <ul class="mt-3 space-y-2 text-slate-700">
                                @foreach ($package->listOf('includes') as $item)
                                    <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 text-wa" /> {{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($package->listOf('excludes'))
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-semibold text-brand-dark">Tidak termasuk</h3>
                            <ul class="mt-3 space-y-2 text-slate-700">
                                @foreach ($package->listOf('excludes') as $item)
                                    <li class="flex gap-2"><x-site-icon name="x-mark" class="mt-0.5 text-brand-red" /> {{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if ($package->listOf('requirements'))
                <section aria-labelledby="persyaratan">
                    <h2 id="persyaratan" class="font-serif text-2xl font-bold text-brand-dark">Persyaratan pendaftaran</h2>
                    <ol class="mt-4 space-y-3">
                        @foreach ($package->listOf('requirements') as $item)
                            <li class="flex gap-3 text-slate-700"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-bold text-white">{{ $loop->iteration }}</span> <span class="pt-0.5">{{ $item }}</span></li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if ($package->mentor)
                <section aria-labelledby="pembimbing">
                    <h2 id="pembimbing" class="font-serif text-2xl font-bold text-brand-dark">Pembimbing ibadah</h2>
                    <div class="mt-4"><x-mentor-card :mentor="$package->mentor" /></div>
                </section>
            @endif

            @unless ($departed)
                <section aria-labelledby="cara-daftar">
                    <h2 id="cara-daftar" class="font-serif text-2xl font-bold text-brand-dark">Cara mendaftar</h2>
                    <ol class="mt-4 space-y-3 text-slate-700">
                        <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gold-light text-sm font-bold text-brand-dark">1</span> <span class="pt-0.5">Hubungi kami lewat WhatsApp dan sebutkan paket ini serta tipe kamar pilihan Anda.</span></li>
                        <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gold-light text-sm font-bold text-brand-dark">2</span> <span class="pt-0.5">Siapkan dokumen yang diminta tim kami.</span></li>
                        <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gold-light text-sm font-bold text-brand-dark">3</span> <span class="pt-0.5">
                            @if ($isHaji)
                                Setoran awal dan pelunasan Bipih Khusus dibayarkan ke bank penerima setoran atas nama Menteri, dan bukti setorannya memuat nomor porsi Anda. Pembayaran lain kepada kami hanya ke rekening atas nama {{ config('site.legal_name') }}.
                            @else
                                Lakukan pembayaran hanya ke rekening atas nama {{ config('site.legal_name') }}, lalu simpan bukti setorannya.
                            @endif
                            <a href="{{ route('legal') }}#rekening" class="font-semibold text-brand underline underline-offset-2">Lihat rekening resmi</a>
                        </span></li>
                    </ol>
                </section>
            @endunless
        </div>

        {{-- Price box: sticky on desktop; on phones a bar at the bottom does the same job. --}}
        <aside class="hidden lg:sticky lg:top-6 lg:block" aria-label="Ringkasan harga">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @if ($lowest)
                    <p class="text-sm text-slate-500">Mulai</p>
                    <p class="text-3xl font-bold text-brand-dark">{{ $package->money($lowest['amount']) }}</p>
                    <p class="text-sm text-slate-500">per orang, {{ Str::lower($lowest['note']) }}</p>
                @endif
                @if ($package->seats_left === 0)
                    <p class="mt-3 inline-flex rounded-full bg-slate-700 px-3 py-1 text-sm font-bold text-white">Kursi penuh</p>
                @elseif ($package->seats_left !== null)
                    <p class="mt-3 inline-flex rounded-full bg-brand-red px-3 py-1 text-sm font-bold text-white">Sisa {{ $package->seats_left }} kursi</p>
                @endif
                @unless ($departed)
                    <x-whatsapp-link :package="$package" class="mt-5 flex min-h-12 items-center justify-center gap-2 rounded-full bg-wa px-6 font-semibold text-white hover:bg-wa-dark">
                        <x-site-icon name="whatsapp" /> Tanya &amp; daftar
                    </x-whatsapp-link>
                @endunless
                <p class="mt-4 text-xs leading-relaxed text-slate-500">Pesan WhatsApp sudah terisi nama paket dan tanggalnya, jadi tim kami langsung tahu yang Anda tanyakan.</p>
            </div>
        </aside>
    </div>

    @if ($others->isNotEmpty())
        <section class="bg-cream" aria-labelledby="jadwal-lain">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <h2 id="jadwal-lain" class="font-serif text-2xl font-bold text-brand-dark">Jadwal lainnya</h2>
                <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($others as $other)
                        <li><x-package-card :package="$other" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Phone: price and WhatsApp always within thumb reach. --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(15,42,90,0.08)] backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-2">
            @if ($lowest)
                <p class="leading-tight">
                    <span class="block text-xs text-slate-500">Mulai</span>
                    <span class="block text-lg font-bold whitespace-nowrap text-brand-dark">{{ $package->money($lowest['amount']) }}</span>
                </p>
            @endif
            @if ($departed)
                <a href="{{ $listing[1] }}" class="inline-flex min-h-12 shrink-0 items-center gap-2 rounded-full bg-brand px-5 font-semibold whitespace-nowrap text-white">Jadwal terbaru</a>
            @else
                <x-whatsapp-link :package="$package" class="inline-flex min-h-12 shrink-0 items-center gap-2 rounded-full bg-wa px-5 font-semibold whitespace-nowrap text-white hover:bg-wa-dark">
                    <x-site-icon name="whatsapp" /> Tanya &amp; daftar
                </x-whatsapp-link>
            @endif
        </div>
    </div>
</x-layouts.site>
