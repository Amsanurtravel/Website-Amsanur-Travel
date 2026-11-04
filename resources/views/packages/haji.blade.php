@php
    $permit = collect(config('site.permits'))->firstWhere('code', 'PIHK');
@endphp

<x-layouts.site
    title="Haji Khusus (Haji Plus)"
    :description="'Haji khusus '.config('site.brand').', berizin '.($permit['short'] ?? 'PIHK').'. Kuota resmi, nomor porsi atas nama jemaah, syarat, setoran awal, masa tunggu, dan harga paket.'"
    :canonical="route('haji.index')"
    :schema="$schema"
>
    <div class="bg-pattern text-white">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" tone="text-slate-300" />
            <h1 class="mt-3 font-serif text-4xl font-bold md:text-5xl">Haji Khusus <span class="text-gold-light">(Haji Plus)</span></h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-slate-200">Haji dengan kuota resmi pemerintah yang diselenggarakan travel berizin PIHK. Nomor porsi terdaftar atas nama jemaah, dengan masa tunggu yang lebih singkat daripada haji reguler.</p>
            @if ($permit)
                <p class="mt-5 inline-flex items-start gap-2 rounded-2xl border border-gold-light/40 bg-white/5 px-4 py-2 text-sm">
                    <x-site-icon name="shield-check" class="mt-0.5 size-4 text-gold-light" />
                    <span><span class="font-semibold text-gold-light">{{ $permit['code'] }} Nomor {{ $permit['number'] }}</span> &middot; {{ config('site.legal_name') }}</span>
                </p>
            @endif
        </div>
    </div>

    <section class="mx-auto max-w-6xl px-4 py-12" aria-labelledby="paket-haji">
        <h2 id="paket-haji" class="font-serif text-3xl font-bold text-brand-dark">Paket haji khusus</h2>
        @if ($packages->isEmpty())
            <p class="mt-4 rounded-2xl bg-cream p-6 text-slate-700">Rincian paket haji khusus terbaru sedang disiapkan. Tanyakan kepada tim kami lewat WhatsApp.</p>
        @else
            <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    <li><x-package-card :package="$package" /></li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="bg-cream" aria-labelledby="jenis-haji">
        <div class="mx-auto max-w-6xl px-4 py-12">
            <h2 id="jenis-haji" class="font-serif text-3xl font-bold text-brand-dark">Haji reguler, haji khusus, dan haji furoda</h2>
            <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Ketiganya sama-sama ibadah haji, tetapi jalur pendaftaran, kuota, dan risikonya berbeda. Pahami dulu sebelum memilih.</p>

            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="font-serif text-xl font-bold text-brand-dark">Haji reguler</h3>
                    <ul class="mt-4 space-y-2 text-sm leading-relaxed text-slate-700">
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Kuota resmi pemerintah, didaftarkan lewat kantor pemerintah</li>
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Biaya paling terjangkau</li>
                        <li class="flex gap-2"><x-site-icon name="clock" class="mt-0.5 size-4 text-gold-dark" /> Masa tunggu paling lama</li>
                    </ul>
                </article>
                <article class="rounded-2xl border-2 border-brand bg-white p-6">
                    <p class="text-xs font-bold tracking-wide text-brand uppercase">Program kami</p>
                    <h3 class="mt-1 font-serif text-xl font-bold text-brand-dark">Haji khusus</h3>
                    <ul class="mt-4 space-y-2 text-sm leading-relaxed text-slate-700">
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Kuota resmi pemerintah lewat travel berizin PIHK</li>
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Nomor porsi atas nama jemaah</li>
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Masa tunggu lebih singkat dan layanan lebih lengkap</li>
                    </ul>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="font-serif text-xl font-bold text-brand-dark">Haji furoda</h3>
                    <ul class="mt-4 space-y-2 text-sm leading-relaxed text-slate-700">
                        <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 size-4 text-wa" /> Memakai visa haji nonkuota yang sepenuhnya ditentukan Arab Saudi, di luar kuota Indonesia</li>
                        <li class="flex gap-2"><x-site-icon name="warning" class="mt-0.5 size-4 text-brand-red" /> Kepastiannya rendah: pada 2025 dan 2026 Arab Saudi tidak menerbitkan visa furoda</li>
                        <li class="flex gap-2"><x-site-icon name="warning" class="mt-0.5 size-4 text-brand-red" /> Menurut aturan tetap wajib berangkat melalui travel berizin PIHK</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12" aria-labelledby="tahapan">
        <h2 id="tahapan" class="font-serif text-3xl font-bold text-brand-dark">Tahapan mendaftar haji khusus</h2>
        <ol class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Konsultasi', 'Tanyakan program, perkiraan masa tunggu, dan biaya lewat WhatsApp.'],
                ['Kirim dokumen', 'KTP, Kartu Keluarga, akta lahir atau akta nikah, dan paspor bila sudah ada.'],
                ['Setoran awal', 'Setoran awal Bipih Khusus dibayarkan ke bank penerima setoran atas nama Menteri. Bukti setorannya memuat nomor porsi Anda.'],
                ['Pelunasan', 'Pelunasan mengikuti tahun keberangkatan sesuai nomor porsi; visa haji diurus setelah pelunasan.'],
            ] as [$step, $text])
                <li class="rounded-2xl border border-slate-200 p-5">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand font-bold text-white">{{ $loop->iteration }}</span>
                    <h3 class="mt-3 font-semibold text-brand-dark">{{ $step }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mx-auto max-w-3xl px-4 pb-12" aria-labelledby="faq-haji">
        <h2 id="faq-haji" class="font-serif text-3xl font-bold text-brand-dark">Tanya jawab haji khusus</h2>
        <x-faq-list :items="$faqs" class="mt-6" />
    </section>

    <section class="bg-pattern text-white">
        <div class="mx-auto max-w-3xl px-4 py-14 text-center">
            <h2 class="font-serif text-3xl font-bold">Ingin tahu perkiraan tahun keberangkatan Anda?</h2>
            <p class="mt-3 text-slate-200">Tim kami menjelaskan syarat dan langkahnya lewat WhatsApp.</p>
            <x-whatsapp-link source="haji-khusus" class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-wa px-8 font-semibold text-white hover:bg-wa-dark">
                <x-site-icon name="whatsapp" /> Tanya haji khusus
            </x-whatsapp-link>
        </div>
    </section>
</x-layouts.site>
