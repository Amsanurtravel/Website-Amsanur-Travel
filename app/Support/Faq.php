<?php

namespace App\Support;

/**
 * Frequently asked questions, rendered on the page and as FAQPage JSON-LD
 * from the same text. Facts come from the client's brochures (November 2026)
 * and the official sources cited on the Legalitas page; keep both in sync
 * when the client changes a package.
 */
class Faq
{
    /**
     * @return list<array{q: string, a: string, id?: string, open?: bool}>
     */
    public static function home(): array
    {
        return [
            [
                'q' => 'Apakah Amsa Nur memiliki izin resmi?',
                'a' => 'Ya. '.config('site.legal_name').' memegang izin '.static::permit('PPIU').' untuk umroh dan izin '.static::permit('PIHK').' untuk haji khusus. Anda bisa mengecek sendiri keaslian izin tersebut; caranya ada di halaman Legalitas.',
            ],
            [
                'q' => 'Apa saja yang sudah termasuk dalam harga paket umroh?',
                'a' => 'Pada paket umroh ekonomis kami: tiket pesawat kelas ekonomi pulang-pergi, visa umroh, hotel sesuai paket, manasik dan bermalam di hotel Jakarta sebelum berangkat, serta umroh tiga kali. Rincian lengkap setiap keberangkatan ada di halaman paketnya, karena isinya bisa sedikit berbeda.',
            ],
            [
                'q' => 'Biaya apa yang belum termasuk?',
                'a' => 'Transportasi dari luar Jakarta, paspor dan vaksin, keperluan pribadi, serta tur di luar program. Selain itu ada biaya perlengkapan dan asuransi Rp 1.750.000 per orang di luar harga paket umroh.',
            ],
            [
                'q' => 'Dokumen apa yang perlu disiapkan untuk umroh?',
                'a' => 'Paspor yang masih berlaku minimal 6 bulan dari tanggal keberangkatan, bukti vaksin meningitis dan polio, surat keterangan sehat dari dokter, serta kartu BPJS Kesehatan (JKN) yang aktif. Visa umroh kami urus sebagai bagian dari paket.',
            ],
            [
                'q' => 'Apa bedanya harga quad, triple, dan double?',
                'a' => 'Harga dihitung per orang sesuai jumlah penghuni kamar hotel: quad berarti sekamar berempat, triple sekamar bertiga, dan double sekamar berdua. Semakin sedikit penghuni kamar, semakin tinggi harganya.',
            ],
            [
                'q' => 'Apakah harga paket bisa berubah?',
                'a' => 'Bisa. Harga dapat berubah sewaktu-waktu menyesuaikan kebijakan Kerajaan Arab Saudi dan maskapai. Harga yang berlaku dikonfirmasi oleh tim kami saat Anda mendaftar.',
            ],
            [
                'q' => 'Bagaimana cara mendaftar?',
                'a' => 'Hubungi kami lewat WhatsApp resmi dan sebutkan paket serta tanggal keberangkatan yang Anda pilih. Tim kami akan menjelaskan dokumen yang perlu disiapkan dan cara pembayarannya.',
            ],
            [
                'q' => 'Ke rekening mana saya membayar?',
                'a' => 'Sesuai aturan, biaya umroh disetor ke rekening penampungan atas nama '.config('site.legal_name').' di bank penerima setoran, dan Anda menerima bukti setorannya. Jangan mentransfer ke rekening pribadi atas nama siapa pun, dan pastikan nomor rekening lewat nomor WhatsApp resmi yang tercantum di situs ini.',
            ],
        ];
    }

    /**
     * @return list<array{q: string, a: string, id?: string, open?: bool}>
     */
    public static function legal(): array
    {
        return [
            [
                'q' => 'Apa itu PPIU dan PIHK?',
                'a' => 'PPIU (Penyelenggara Perjalanan Ibadah Umrah) adalah izin bagi travel untuk memberangkatkan jemaah umroh. PIHK (Penyelenggara Ibadah Haji Khusus) adalah izin untuk menyelenggarakan haji khusus. Travel tanpa izin tersebut tidak boleh memberangkatkan jemaah.',
            ],
            [
                // Target of the "cek izin" links in the header, footer and home page.
                'id' => 'cara-cek-izin',
                // Shown open: browsers do not reliably expand a <details> on #fragment navigation.
                'open' => true,
                'q' => 'Bagaimana cara mengecek izin travel?',
                'a' => 'Lewat aplikasi resmi Kementerian Haji dan Umrah, SatuHaji: buka Layanan, pilih Umrah & Haji Khusus, lalu Penyelenggara Umrah (PPIU), dan cari nama travel. Bisa juga lewat halaman publik SISKOPATUH. Pastikan nama perusahaan, nomor izin, status aktif, dan akreditasinya cocok.',
            ],
            [
                'q' => 'Mengapa saya perlu mengecek izin travel sendiri?',
                'a' => 'Karena nomor izin bisa saja dicantumkan oleh pihak yang tidak berhak. Dengan mengecek sendiri lewat kanal resmi pemerintah, Anda memastikan nama travel, nomor izin, dan statusnya cocok sebelum membayar.',
            ],
            [
                'q' => 'Bagaimana memastikan pembayaran saya aman?',
                'a' => 'Bayar hanya ke rekening atas nama '.config('site.legal_name').', simpan bukti transfer, dan minta kuitansi resmi. Jika ada yang meminta transfer ke rekening pribadi atau menawarkan harga yang jauh di bawah kewajaran, hentikan dan hubungi nomor resmi kami.',
            ],
        ];
    }

    /**
     * @return list<array{q: string, a: string, id?: string, open?: bool}>
     */
    public static function haji(): array
    {
        return [
            [
                'q' => 'Apa itu haji khusus (haji plus)?',
                'a' => 'Haji khusus adalah haji dengan kuota resmi pemerintah yang diselenggarakan oleh travel berizin PIHK. Jemaah mendapat nomor porsi resmi, masa tunggu yang lebih singkat daripada haji reguler, dan layanan yang lebih lengkap.',
            ],
            [
                'q' => 'Berapa lama daftar tunggu haji khusus di Amsa Nur?',
                'a' => 'Perkiraan antrean saat ini sekitar 6 sampai 7 tahun sejak nomor porsi terbit. Tahun keberangkatan mengikuti nomor porsi dan kuota yang ditetapkan pemerintah.',
            ],
            [
                'q' => 'Bagaimana pembayaran setoran awal haji khusus?',
                'a' => 'Sesuai aturan pemerintah, setoran awal Bipih Khusus (USD 4.000 per Januari 2026) dibayarkan ke bank penerima setoran atas nama Menteri, bukan ke rekening travel, dan bukti setorannya memuat nomor porsi Anda. Pelunasan dilakukan mengikuti tahun keberangkatan. Biaya layanan paket kami dijelaskan terpisah oleh tim kami sebelum Anda menyetor.',
            ],
            [
                'q' => 'Apa syarat mendaftar haji khusus?',
                'a' => 'Beragama Islam, berusia minimal 12 tahun saat mendaftar, dan terdaftar sebagai peserta aktif BPJS Kesehatan (JKN). Lalu kirimkan KTP, Kartu Keluarga, akta lahir atau akta nikah, dan paspor (bila sudah ada) lewat WhatsApp atau email.',
            ],
            [
                'q' => 'Apa beda haji khusus dan haji furoda?',
                'a' => 'Haji khusus memakai kuota resmi Indonesia dengan nomor porsi dan antrean. Haji furoda memakai visa nonkuota yang sepenuhnya ditentukan Arab Saudi, tanpa nomor porsi, sehingga kepastiannya rendah: pada 2025 dan 2026 Arab Saudi tidak menerbitkan visa furoda. Menurut aturan, jemaah visa nonkuota tetap harus berangkat melalui PIHK berizin.',
            ],
        ];
    }

    private static function permit(string $code): string
    {
        $permit = collect(config('site.permits'))->firstWhere('code', $code);

        return $permit ? "{$permit['code']} Nomor {$permit['number']}" : $code;
    }
}
