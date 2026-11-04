@php
    $site = config('site');
@endphp

<x-layouts.site
    title="Legalitas & Izin Resmi"
    :description="'Izin resmi '.$site['legal_name'].': '.collect($site['permits'])->map(fn ($p) => $p['code'].' Nomor '.$p['number'])->implode(' dan ').'. Data badan hukum, keanggotaan asosiasi, dan rekening resmi untuk pembayaran.'"
    :canonical="route('legal')"
    :schema="$schema"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-4xl px-4 py-8 md:py-12">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" />
            <h1 class="mt-3 font-serif text-4xl font-bold text-brand-dark">Legalitas &amp; izin resmi</h1>
            <p class="mt-3 leading-relaxed text-slate-700">{{ $site['brand'] }} adalah merek dari <strong>{{ $site['legal_name'] }}</strong>. Berikut izin yang kami pegang, data badan hukum perusahaan, dan rekening resmi untuk pembayaran.</p>
        </div>
    </div>

    <div class="mx-auto max-w-4xl space-y-12 px-4 py-10">
        <section aria-labelledby="izin">
            <h2 id="izin" class="font-serif text-2xl font-bold text-brand-dark">Izin usaha</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                @foreach ($site['permits'] as $permit)
                    <article class="rounded-2xl border-2 border-gold/50 bg-white p-6">
                        <p class="flex items-center gap-2 text-sm font-bold tracking-wide text-gold-dark uppercase"><x-site-icon name="shield-check" class="size-5" /> Izin {{ $permit['code'] }}</p>
                        <h3 class="mt-2 font-semibold text-brand-dark">{{ $permit['name'] }}</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500">Nomor izin</dt>
                                <dd class="font-serif text-2xl font-bold text-brand-dark">{{ $permit['number'] }}</dd>
                                @if ($permit['date'])
                                    <dd class="text-slate-600">Tanggal {{ $permit['date'] }}</dd>
                                @endif
                            </div>
                            <div>
                                <dt class="text-slate-500">Atas nama</dt>
                                <dd class="font-semibold text-slate-800">{{ $site['legal_name'] }}</dd>
                            </div>
                            @if ($permit['accreditation'])
                                <div>
                                    <dt class="text-slate-500">Akreditasi</dt>
                                    <dd class="font-semibold text-slate-800">{{ $permit['accreditation'] }}</dd>
                                </div>
                            @endif
                            @if ($since = $site['licensed_since'][$permit['code']] ?? null)
                                <div>
                                    <dt class="text-slate-500">Berizin sejak</dt>
                                    <dd class="font-semibold text-slate-800">{{ $since }}</dd>
                                </div>
                            @endif
                        </dl>
                        @if ($permit['registry_note'] ?? null)
                            <p class="mt-4 rounded-xl bg-cream p-3 text-sm leading-relaxed text-slate-700">{{ $permit['registry_note'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
            <p class="mt-5 text-sm leading-relaxed text-slate-600">Izin diterbitkan {{ $site['permit_issuer'] }} dan kini diawasi {{ $site['permit_supervisor'] }} sesuai UU Nomor 14 Tahun 2025. Status izin dan akreditasi di atas dicocokkan dengan data publik SISKOPATUH Kementerian Haji dan Umrah pada {{ $site['permits_checked_at'] }}.</p>
        </section>

        @if ($site['company_documents'] || $site['memberships'])
            <section aria-labelledby="perusahaan" class="grid gap-5 md:grid-cols-2">
                <h2 id="perusahaan" class="sr-only">Badan hukum dan keanggotaan</h2>
                @if ($site['company_documents'])
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-semibold text-brand-dark">Badan hukum</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            @foreach ($site['company_documents'] as $document)
                                <div>
                                    <dt class="text-slate-500">{{ $document['label'] }}</dt>
                                    <dd class="font-semibold text-slate-800">{{ $document['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
                @if ($site['memberships'])
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-semibold text-brand-dark">Keanggotaan asosiasi</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            @foreach ($site['memberships'] as $membership)
                                <div>
                                    <dt class="text-slate-500"><abbr title="{{ $membership['name'] }}" class="no-underline">{{ $membership['code'] }}</abbr> &middot; {{ $membership['name'] }}</dt>
                                    <dd class="font-semibold text-slate-800">Nomor anggota {{ $membership['number'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </section>
        @endif

        @if ($site['bank_accounts'])
            <section id="rekening" aria-labelledby="rekening-judul" class="scroll-mt-6">
                <h2 id="rekening-judul" class="font-serif text-2xl font-bold text-brand-dark">Rekening resmi</h2>
                <p class="mt-3 leading-relaxed text-slate-700">Pembayaran ke kami hanya ke rekening atas nama <strong>{{ $site['legal_name'] }}</strong>. Kami tidak pernah meminta transfer ke rekening pribadi.</p>
                <ul class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($site['bank_accounts'] as $account)
                        <li class="rounded-2xl border border-slate-200 p-5">
                            <p class="text-sm text-slate-500">{{ $account['bank'] }} &middot; {{ $account['currency'] }}</p>
                            <p class="mt-1 font-mono text-2xl font-bold tracking-wider text-brand-dark">{{ $account['number'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">a.n. {{ $account['holder'] }}</p>
                        </li>
                    @endforeach
                </ul>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed text-slate-600">
                    <li>Pastikan nama pemilik rekening yang muncul saat transfer adalah {{ $site['legal_name'] }}. Bila ragu, tanyakan dulu lewat nomor WhatsApp resmi sebelum mentransfer.</li>
                    <li>Untuk haji khusus, setoran awal dan pelunasan Bipih Khusus dibayarkan ke bank penerima setoran atas nama Menteri sesuai aturan pemerintah, dan bukti setorannya memuat nomor porsi Anda. Tim kami memandu prosesnya.</li>
                </ul>
            </section>
        @endif

        <section aria-labelledby="kanal-resmi">
            <h2 id="kanal-resmi" class="font-serif text-2xl font-bold text-brand-dark">Kanal resmi kami</h2>
            <ul class="mt-5 space-y-3 text-slate-700">
                <li>
                    <x-whatsapp-link class="inline-flex min-h-11 items-center gap-2 font-semibold text-wa hover:underline">
                        <x-site-icon name="whatsapp" /> WhatsApp {{ $site['phone'] }}
                    </x-whatsapp-link>
                </li>
                @if ($site['director_whatsapp'])
                    <li><a href="https://wa.me/{{ \App\Support\WhatsApp::number($site['director_whatsapp']) }}" class="inline-flex min-h-11 items-center gap-2 font-semibold text-wa hover:underline" rel="nofollow noopener" target="_blank"><x-site-icon name="whatsapp" /> WhatsApp Direktur {{ $site['director_whatsapp'] }}</a></li>
                @endif
                @if ($site['office']['phone'])
                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $site['office']['phone']) }}" class="inline-flex min-h-11 items-center gap-2 font-semibold text-brand hover:underline"><x-site-icon name="phone" /> Kantor {{ $site['office']['phone'] }}</a></li>
                @endif
                @if ($site['email'])
                    <li><a href="mailto:{{ $site['email'] }}" class="inline-flex min-h-11 items-center gap-2 font-semibold text-brand hover:underline"><x-site-icon name="envelope" /> {{ $site['email'] }}</a></li>
                @endif
                @if ($site['office']['address'])
                    <li class="flex gap-2"><x-site-icon name="map-pin" class="mt-0.5 shrink-0 text-brand" /> <span><strong>{{ $site['office']['name'] }}</strong>: {{ $site['office']['address'] }}</span></li>
                @endif
                @if ($site['office']['hours'])
                    <li class="flex gap-2"><x-site-icon name="clock" class="mt-0.5 shrink-0 text-brand" /> <x-office-hours /></li>
                @endif
                @foreach ($site['socials'] as $network => $url)
                    <li><a href="{{ $url }}" class="inline-flex min-h-11 items-center gap-2 font-semibold text-brand hover:underline" rel="noopener" target="_blank"><x-site-icon :name="Str::lower($network)" /> {{ $network }}: {{ Str::of($url)->after('.com/')->rtrim('/') }} <x-site-icon name="external" class="size-4" /></a></li>
                @endforeach
            </ul>
            @if ($hasRepresentatives)
                <p class="mt-4 leading-relaxed text-slate-700">Perwakilan resmi kami di daerah tercantum di halaman <a href="{{ route('representatives') }}" class="font-semibold text-brand underline underline-offset-2">Perwakilan Daerah</a>. Pembayaran lewat perwakilan pun tetap ke rekening atas nama {{ $site['legal_name'] }}.</p>
            @endif
        </section>

        <section aria-labelledby="waspada" class="rounded-2xl border border-brand-red/30 bg-red-50 p-6">
            <h2 id="waspada" class="flex items-center gap-2 font-serif text-2xl font-bold text-brand-dark"><x-site-icon name="warning" class="size-6 text-brand-red" /> Waspada penipuan</h2>
            <p class="mt-3 text-slate-700">Empat tips dari Kementerian Haji dan Umrah sebelum memilih travel:</p>
            <ol class="mt-3 space-y-2 leading-relaxed text-slate-700">
                <li class="flex gap-3"><span class="font-bold text-brand-red">1.</span> <span><strong>Cek legalitas</strong>: izin dan akreditasi travel lewat aplikasi SatuHaji.</span></li>
                <li class="flex gap-3"><span class="font-bold text-brand-red">2.</span> <span><strong>Periksa reputasi</strong>: rekam jejak dan pengalaman jemaah sebelumnya.</span></li>
                <li class="flex gap-3"><span class="font-bold text-brand-red">3.</span> <span><strong>Teliti dokumen dan biaya</strong>: perjanjian tertulis, rincian harga, dan waspadai harga yang tidak wajar.</span></li>
                <li class="flex gap-3"><span class="font-bold text-brand-red">4.</span> <span><strong>Pilih layanan yang standar</strong>: jadwal, maskapai, hotel, dan visa yang jelas.</span></li>
            </ol>
            <p class="mt-4 text-sm leading-relaxed text-slate-700">Menemukan kejanggalan? Jangan membayar dulu. Tanyakan ke nomor resmi kami, atau laporkan ke hotline pengaduan Kemenhaj <strong>{{ $site['complaints']['hotline'] }}</strong> dan <a href="mailto:{{ $site['complaints']['email'] }}" class="font-semibold text-brand underline">{{ $site['complaints']['email'] }}</a>.</p>
        </section>

        <section aria-labelledby="faq-legal">
            <h2 id="faq-legal" class="font-serif text-2xl font-bold text-brand-dark">Tanya jawab</h2>
            <x-faq-list :items="$faqs" class="mt-5" />
        </section>
    </div>
</x-layouts.site>
