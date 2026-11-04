@php
    $site = config('site');
@endphp

<x-layouts.site
    title="Tentang Kami"
    :description="$site['brand'].' adalah merek dari '.$site['legal_name'].', penyelenggara umroh dan haji khusus di Jakarta sejak '.$site['founded'].', berizin PPIU dan PIHK. Visi, layanan, pembimbing ibadah, dan kontak kami.'"
    :canonical="route('about')"
    :schema="$schema"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-4xl px-4 py-8 md:py-12">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" />
            <h1 class="mt-3 font-serif text-4xl font-bold text-brand-dark">Tentang Kami</h1>
        </div>
    </div>

    <div class="mx-auto max-w-4xl space-y-12 px-4 py-10">
        <section aria-labelledby="profil" class="grid gap-8 md:grid-cols-[auto_1fr] md:items-start">
            <img src="{{ asset('images/amsa-nur-logo.png') }}" alt="Logo {{ $site['brand'] }}" width="452" height="147" class="h-auto w-48">
            <div>
                <h2 id="profil" class="font-serif text-2xl font-bold text-brand-dark">Melayani tamu Allah sejak {{ $site['founded'] }}</h2>
                <p class="mt-3 leading-relaxed text-slate-700">
                    {{ $site['brand'] }} adalah merek dari {{ $site['legal_name'] }}, perusahaan yang didirikan di Jakarta pada {{ $site['founded'] }} dan bergerak di bidang pelayanan ibadah umroh dan haji khusus.
                    Izin umroh pertama kami terbit pada {{ $site['licensed_since']['PPIU'] }} dan izin haji khusus pada {{ $site['licensed_since']['PIHK'] }}. Saat ini kami memegang izin {{ collect($site['permits'])->map(fn ($permit) => $permit['code'].' Nomor '.$permit['number'].' ('.Str::lcfirst($permit['name']).')')->implode(' dan ') }}.
                </p>
                <p class="mt-3 leading-relaxed text-slate-700">Kantor pusat kami menempati gedung milik sendiri di Jl. Pramuka Raya, Jakarta Pusat. Perusahaan dipimpin oleh Direktur {{ $site['director'] }}, yang juga membimbing langsung jemaah sebagai pembimbing ibadah dan mengasuh Pesantren Tadabbur Al-Qur'an Amsa di Gadog, Megamendung, Kabupaten Bogor.</p>
                <blockquote class="mt-6 border-l-4 border-gold pl-4 font-serif text-xl leading-relaxed text-brand-dark italic">&ldquo;{{ $site['tagline'] }}&rdquo;</blockquote>
            </div>
        </section>

        <section aria-labelledby="visi" class="grid gap-5 md:grid-cols-2">
            <div class="rounded-2xl bg-cream p-6">
                <h2 id="visi" class="font-serif text-2xl font-bold text-brand-dark">Visi</h2>
                <p class="mt-3 leading-relaxed text-slate-700">Menjadi perusahaan yang profesional, berkualitas, dan terdepan dalam memberikan layanan terbaik kepada seluruh calon jemaah haji dan umroh di mana pun mereka berada.</p>
            </div>
            <div class="rounded-2xl bg-cream p-6">
                <h2 class="font-serif text-2xl font-bold text-brand-dark">Misi</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 leading-relaxed text-slate-700">
                    <li>Memberikan pelayanan terbaik kepada seluruh jemaah dengan hati ikhlas.</li>
                    <li>Peduli terhadap kebutuhan jemaah sebagai orientasi layanan kami.</li>
                    <li>Menjadikan kepuasan jemaah prioritas kami.</li>
                    <li>Memberi nilai lebih lewat manajemen dan semangat kerja berlandaskan sifat Nabi: <em>shiddiq, amanah, fathonah, tabligh</em> (jujur, bertanggung jawab, profesional, dan transparan).</li>
                </ol>
            </div>
        </section>

        <section aria-labelledby="layanan">
            <h2 id="layanan" class="font-serif text-2xl font-bold text-brand-dark">Pelayanan kami</h2>
            <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'Administrasi pendaftaran yang cepat dan mudah',
                    'Bimbingan manasik haji dan umroh di tanah air',
                    'Bimbingan penuh selama ibadah di Tanah Suci',
                    'Pembinaan pascahaji di tanah air',
                    'Layanan kesehatan bagi jemaah yang membutuhkan',
                    'Transportasi ber-AC selama di Tanah Suci',
                    'Tenda ber-AC selama di Arafah dan Mina (haji)',
                    'Piagam umroh atau haji sebagai cendera mata',
                ] as $service)
                    <li class="flex gap-3 rounded-xl border border-slate-200 p-4 text-slate-700"><x-site-icon name="check" class="mt-0.5 shrink-0 text-wa" /> {{ $service }}</li>
                @endforeach
            </ul>
            <p class="mt-3 text-sm text-slate-600">Hotel, maskapai, dan fasilitas pasti setiap keberangkatan tertulis di halaman paketnya.</p>
        </section>

        <section aria-labelledby="umroh-plus" class="rounded-2xl border-2 border-gold/50 p-6">
            <h2 id="umroh-plus" class="font-serif text-2xl font-bold text-brand-dark">Umroh plus dan rombongan</h2>
            <p class="mt-3 leading-relaxed text-slate-700">Selain jadwal reguler, kami melayani program berdasarkan permintaan:</p>
            <ul class="mt-3 grid gap-2 text-slate-700 sm:grid-cols-2">
                @foreach (['Umroh plus Istanbul', 'Umroh plus Yaman (Hadramaut)', 'Umroh pelajar', 'Umroh perusahaan dan organisasi'] as $program)
                    <li class="flex gap-2"><x-site-icon name="sparkles" class="mt-0.5 shrink-0 text-gold-dark" /> {{ $program }}</li>
                @endforeach
            </ul>
            <p class="mt-4 text-sm leading-relaxed text-slate-600">Umroh plus dibuka untuk rombongan minimal 35 jemaah, dengan pendaftaran paling lambat 1 bulan sebelum keberangkatan.</p>
            <x-whatsapp-link class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-full bg-wa px-5 font-semibold text-white hover:bg-wa-dark">
                <x-site-icon name="whatsapp" /> Tanyakan program rombongan
            </x-whatsapp-link>
        </section>

        @if (config('site.gallery'))
            <section id="dokumentasi" aria-labelledby="dokumentasi-judul" class="scroll-mt-6">
                <h2 id="dokumentasi-judul" class="font-serif text-2xl font-bold text-brand-dark">Dokumentasi jemaah</h2>
                <p class="mt-2 text-slate-700">Rombongan jemaah Amsa Nur pada perjalanan umroh sebelumnya.</p>
                <x-jemaah-gallery class="mt-5 md:grid-cols-3" sizes="(min-width: 896px) 280px, (min-width: 768px) 33vw, 50vw" />
            </section>
        @endif

        @if ($mentors->isNotEmpty())
            <section aria-labelledby="pembimbing">
                <h2 id="pembimbing" class="font-serif text-2xl font-bold text-brand-dark">Pembimbing ibadah</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    @foreach ($mentors as $mentor)
                        <x-mentor-card :mentor="$mentor" />
                    @endforeach
                </div>
            </section>
        @endif

        <section id="kontak" aria-labelledby="kontak-judul" class="scroll-mt-6 rounded-3xl bg-pattern p-6 text-white md:p-10">
            <h2 id="kontak-judul" class="font-serif text-2xl font-bold">Hubungi kami</h2>
            <p class="mt-2 text-slate-200">Untuk pertanyaan paket, jadwal, dan pendaftaran.</p>
            <ul class="mt-6 space-y-4">
                <li>
                    <x-whatsapp-link class="inline-flex min-h-12 items-center gap-2 rounded-full bg-wa px-6 font-semibold text-white hover:bg-wa-dark">
                        <x-site-icon name="whatsapp" /> WhatsApp {{ $site['phone'] }}
                    </x-whatsapp-link>
                </li>
                @if ($site['office']['phone'])
                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $site['office']['phone']) }}" class="inline-flex min-h-11 items-center gap-2 hover:underline"><x-site-icon name="phone" /> Kantor {{ $site['office']['phone'] }}</a></li>
                @endif
                @if ($site['email'])
                    <li><a href="mailto:{{ $site['email'] }}" class="inline-flex min-h-11 items-center gap-2 hover:underline"><x-site-icon name="envelope" /> {{ $site['email'] }}</a></li>
                @endif
                <li class="flex gap-2">
                    <x-site-icon name="map-pin" class="mt-0.5 text-gold-light" />
                    <span>
                        <span class="font-semibold">{{ $site['office']['name'] }}</span>
                        @if ($site['office']['address'])
                            <br>{{ $site['office']['address'] }}
                        @endif
                        @if ($site['office']['hours'])
                            <x-office-hours class="mt-3 text-sm text-slate-200" />
                        @endif
                        @if ($site['office']['maps_url'])
                            <br><a href="{{ $site['office']['maps_url'] }}" class="inline-flex min-h-11 items-center gap-1 font-semibold text-gold-light underline underline-offset-2" rel="noopener" target="_blank">Buka di Google Maps <x-site-icon name="external" class="size-4" /></a>
                        @endif
                    </span>
                </li>
            </ul>
            <x-social-links class="mt-6" />
        </section>
    </div>
</x-layouts.site>
