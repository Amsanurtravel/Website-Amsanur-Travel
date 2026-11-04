@php
    $site = config('site');
@endphp

<x-layouts.site
    title="Kebijakan Privasi"
    :description="'Cara '.$site['legal_name'].' mengumpulkan, memakai, dan melindungi data pribadi calon jemaah sesuai UU Pelindungan Data Pribadi.'"
    :canonical="route('privacy')"
    :schema="$schema"
>
    <div class="bg-cream">
        <div class="mx-auto max-w-3xl px-4 py-8 md:py-12">
            <x-breadcrumbs :trail="[['Beranda', route('home')]]" />
            <h1 class="mt-3 font-serif text-4xl font-bold text-brand-dark">Kebijakan Privasi</h1>
            <p class="mt-3 leading-relaxed text-slate-700">{{ $site['legal_name'] }} ({{ $site['brand'] }}) menghormati data pribadi Anda dan mengelolanya sesuai Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.</p>
            <p class="mt-2 text-sm text-slate-600">Terakhir diperbarui 3 November 2026.</p>
        </div>
    </div>

    <div class="article-body mx-auto max-w-3xl px-4 py-10">
        <h2>Data yang kami kumpulkan</h2>
        <p>Saat Anda bertanya atau mendaftar lewat WhatsApp, email, atau di kantor, kami dapat meminta:</p>
        <ul>
            <li>identitas: KTP, Kartu Keluarga, akta lahir atau akta nikah, paspor, dan foto;</li>
            <li>kontak: nomor telepon, email, dan alamat;</li>
            <li>data kesehatan yang disyaratkan perjalanan, misalnya bukti vaksin, surat keterangan sehat, hasil pemeriksaan kesehatan, dan kepesertaan BPJS Kesehatan (JKN);</li>
            <li>data anak yang ikut berangkat, yang kami terima dari orang tua atau wali.</li>
        </ul>

        <h2>Untuk apa data dipakai</h2>
        <p>Data hanya dipakai untuk menyelenggarakan perjalanan ibadah Anda: pendaftaran, pengurusan visa, tiket, hotel, asuransi, manasik, serta pelaporan yang diwajibkan pemerintah. Kami tidak menjual data Anda dan tidak memakainya untuk keperluan lain tanpa persetujuan Anda.</p>

        <h2>Siapa yang menerima data</h2>
        <p>Sebatas yang diperlukan, data dapat kami sampaikan kepada Kementerian Haji dan Umrah beserta sistem informasinya, bank penerima setoran, maskapai, perusahaan asuransi, dan penyedia layanan di Arab Saudi, termasuk otoritas visa.</p>

        <h2>Berapa lama data disimpan</h2>
        <p>Data disimpan selama diperlukan untuk penyelenggaraan perjalanan dan untuk memenuhi kewajiban hukum, lalu dihapus atau dimusnahkan dengan aman.</p>

        <h2>Hak Anda</h2>
        <p>Anda berhak meminta akses dan salinan data Anda, memperbaiki data yang keliru, menarik persetujuan, serta meminta penghapusan data yang tidak lagi wajib kami simpan. Testimoni hanya kami tampilkan dengan persetujuan jemaah dan akan kami turunkan bila persetujuannya ditarik.</p>

        <h2>Situs ini</h2>
        <ul>
            <li>Halaman situs ini tidak memasang cookie pelacak.</li>
            <li>Saat Anda menekan tombol WhatsApp, kami hanya mencatat halaman dan paket yang diklik untuk statistik. Alamat IP, perangkat, dan nomor Anda tidak dicatat.</li>
            @if ($site['ga4_id'])
                <li>Kami memakai Google Analytics untuk statistik kunjungan. Layanan ini memasang cookie di peramban Anda.</li>
            @endif
        </ul>

        <h2>Hubungi kami</h2>
        <p>Untuk pertanyaan atau permintaan terkait data pribadi, hubungi kami lewat WhatsApp {{ $site['phone'] }}@if ($site['email']), email <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>@endif, atau datang ke {{ $site['office']['name'] }}@if ($site['office']['address']) di {{ $site['office']['address'] }}@endif.</p>
    </div>
</x-layouts.site>
