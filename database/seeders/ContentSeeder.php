<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\Representative;
use Illuminate\Database\Seeder;

/**
 * Real starting content, transcribed from the client's departure brochures
 * and company profile (received 3 and 4 November 2026). It only fills an
 * empty database: once packages or mentors exist, the admin panel is the
 * source of truth, so a later `db:seed` never brings back a deleted
 * departure or representative, or duplicates a mentor.
 */
class ContentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Package::query()->exists() || Mentor::query()->exists()) {
            return;
        }

        $mentor = Mentor::firstOrCreate(['name' => 'KH. Drs. M. Aminullah Tsamud'], static::mentor());

        foreach (static::packages($mentor) as $slug => $attributes) {
            Package::firstOrCreate(['slug' => $slug], $attributes);
        }

        foreach (static::representatives() as $attributes) {
            Representative::firstOrCreate(['region' => $attributes['region'], 'name' => $attributes['name']], $attributes);
        }

        $this->seedDraftArticles();
    }

    /**
     * The mentor on the brochures ("KH. Drs. M. Aminullah"). The client
     * confirmed on 4 November 2026 that he is the company's director,
     * Drs. H. M. Aminullah Tsamud, and the only mentor for now. The profile
     * follows the user's description; the client may edit it in the admin.
     *
     * @return array<string, string>
     */
    public static function mentor(): array
    {
        return [
            'role' => 'Pembimbing Ibadah',
            'affiliation' => "Pengasuh Pesantren Tadabbur Al-Qur'an Amsa, Gadog, Megamendung, Kabupaten Bogor",
            'bio' => 'Beliau juga Direktur PT Amsa Nur Indah Mandiri dan membimbing langsung jemaah umroh dan haji khusus Amsa Nur.',
            'profile' => implode("\n\n", [
                'KH. Drs. M. Aminullah Tsamud adalah Direktur PT Amsa Nur Indah Mandiri. Selain memimpin perusahaan secara langsung, beliau juga menjadi pembimbing ibadah yang mendampingi jemaah umroh dan haji khusus Amsa Nur.',
                "Beliau mengasuh Pesantren Tadabbur Al-Qur'an Amsa di Gadog, Megamendung, Kabupaten Bogor, yang berdiri sejak 2021. Pesantren ini menjadi tempat para santri menghafal Al-Qur'an, beribadah, belajar, dan berprestasi. Sesuai namanya, santri diajak tidak hanya menghafal, tetapi juga merenungi (tadabbur) dan mengamalkan isi Al-Qur'an dalam keseharian.",
                'Semangat yang sama hadir dalam bimbingan ibadah di Amsa Nur. Jemaah dibimbing untuk memahami tata cara dan makna setiap rangkaian ibadah, sejak manasik di tanah air hingga di Tanah Suci, agar dapat beribadah dengan tenang, khusyuk, dan sesuai tuntunan.',
                "Harapannya, setiap jemaah pulang membawa umroh yang maqbul atau haji yang mabrur, serta semangat untuk semakin dekat dengan Al-Qur'an.",
            ]),
        ];
    }

    /**
     * Regional representatives from the company profile (2024); the client
     * agreed on 4 November 2026 that they may be listed. Regions keep the
     * profile's order and wording.
     *
     * @return list<array<string, mixed>>
     */
    public static function representatives(): array
    {
        $list = [
            ['DKI Jakarta', 'H. Jaetul Muchlis', 'Jl. I.B. Gede No. 3, RT 003/RW 016, Komp. Trikora, Halim Perdana Kusuma, Jakarta Timur'],
            ['DKI Jakarta', 'Ahmad Saeful Uyun, S.Ag', 'Jl. Poncowolo No. 396, RT 002/RW 002, Halim Perdana Kusuma, Kec. Makasar, Jakarta Timur'],
            ['DKI Jakarta', 'Dr. Moh. Rofiq', 'Jl. Duri Raya No. 96A, RT 005/RW 01, Kel. Duri Kepa, Kebon Jeruk, Jakarta Barat'],
            ['DKI Jakarta', 'Ida Rosa', 'Jl. Cempaka Indah No. 15, RT 05/RW 07, Jakarta Pusat'],
            ['Jawa Tengah', 'KBIH Al Wardah (Hj. Laily Mansur dan Hj. Zahratul Jannah)', 'Komp. Kavling GOR Satria, Purwokerto'],
            ['Jawa Timur', 'H. Akhmad Muafi, M.Ag', 'Jl. Ketintang 4C/17, Surabaya, Jawa Timur'],
            ['Madura', 'Hj. Hozaimah', 'Jl. Raya Masalima Masalembo No. 13, Kec. Masalembo, Kab. Sumenep, Madura'],
            ['Madura', 'H. Fahmi Fattah', 'Ponpes Al-Amin Prenduan, Sumenep, Madura'],
            ['Bali', 'H. M. Hud Alqadri', 'Jl. Ceningan Sari XVI, Sesetan, Denpasar, Bali 80223'],
            ['Kalimantan Selatan', 'Hj. Masdinar', 'Jl. Taqwa RT 9 No. 33, Pelaihari, Tanah Laut, Kalimantan Selatan 70811'],
            ['Kalimantan Timur', 'Jimmy Aulia', 'Jl. KH. Akhmad Husin No. 01, Timbau, Tenggarong, Kutai Kartanegara, Kalimantan Timur'],
            ['Kalimantan Timur', 'Akbariah', 'Jl. Pembangunan, Desa Purwajaya RT 3 KM 5, Kec. Loa Janan, Samarinda, Kalimantan Timur'],
            ['Kalimantan Barat', "Faruk Adra'ie", 'Jl. Rahadi Ismail No. 23, Desa Padang, Kec. Benua Kayong, Ketapang, Kalimantan Barat'],
            ['Sumatera Selatan', 'Yosef Harianto, S.Pd.I', 'Jl. Simpang Temam RT 10, Lr. Teratai 2, Kel. Lubuk Kupang, Kec. Lubuklinggau Selatan, Kota Lubuklinggau, Sumatera Selatan'],
            ['Papua', 'Kumaya Sari', 'Jl. Bandeng X/133, RT 33/RW 004, Kel. Klawuyuk, Kec. Sorong Utara, Kota Sorong, Papua'],
        ];

        return array_map(fn (array $row, int $index) => [
            'region' => $row[0],
            'name' => $row[1],
            'address' => $row[2],
            'sort' => ($index + 1) * 10,
            'is_active' => true,
        ], $list, array_keys($list));
    }

    /**
     * The departures on the brochures, keyed by slug.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function packages(Mentor $mentor): array
    {
        $excludes = [
            'Transportasi dari luar Jakarta',
            'Paspor dan vaksin',
            'Keperluan pribadi',
            'Tur di luar program',
        ];

        $includes = [
            'Tiket pesawat kelas ekonomi pulang-pergi',
            'Manasik dan bermalam di hotel Jakarta',
            'Visa umroh',
            'Hotel sesuai paket',
            'Umroh 3 kali',
        ];

        $umrah = [
            'type' => Package::UMRAH,
            'name' => 'Umroh Ekonomis 12 Hari',
            'duration_days' => 12,
            'route_departure' => 'Jakarta - Jeddah',
            'route_return' => 'Jeddah - Jakarta',
            'currency' => 'IDR',
            'extra_costs' => [['label' => 'Perlengkapan dan asuransi', 'amount' => 1_750_000]],
            'highlights' => ['Umroh 3 kali', 'Manasik dan bermalam di hotel Jakarta sebelum berangkat'],
            'excludes' => $excludes,
            'published_at' => now(),
        ];

        return [
            'umroh-ekonomis-12-hari-11-november-2026' => $umrah + [
                'departure_date' => '2026-11-11',
                'hotel_makkah' => 'Wahat Ajyad',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Taibah Hill',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia atau Garuda',
                'price_quad' => 35_000_000,
                'price_triple' => 36_000_000,
                'price_double' => 38_000_000,
                'includes' => $includes,
                'mentor_id' => $mentor->id,
            ],
            'umroh-ekonomis-12-hari-2-desember-2026' => $umrah + [
                'departure_date' => '2026-12-02',
                'hotel_makkah' => 'Wahat Ajyad',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Taibah Hill',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia atau Garuda',
                'price_quad' => 35_000_000,
                'price_triple' => 36_000_000,
                'price_double' => 38_000_000,
                'includes' => [...$includes, 'Transportasi dan konsumsi selama umroh'],
                'mentor_id' => $mentor->id,
            ],
            'umroh-ekonomis-12-hari-16-desember-2026' => $umrah + [
                'departure_date' => '2026-12-16',
                'hotel_makkah' => 'Nada Ajyad atau setaraf',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Taiba Hills atau setaraf',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia',
                'price_quad' => 45_500_000,
                'price_triple' => 46_500_000,
                'price_double' => 48_500_000,
                'includes' => $includes,
                'notes' => 'Harga sewaktu-waktu dapat berubah menyesuaikan kebijakan Kerajaan Arab Saudi dan maskapai.',
                'mentor_id' => $mentor->id,
                // The 16 December brochure prints the director's number (config site.director_whatsapp); buttons stay on the office number.
            ],
            'umroh-ekonomis-12-hari-13-januari-2027' => $umrah + [
                'departure_date' => '2027-01-13',
                'hotel_makkah' => 'Snood Hotel',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Nusk Al Eman',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia atau Garuda',
                'price_quad' => 35_000_000,
                'price_triple' => 36_000_000,
                'price_double' => 38_000_000,
                'includes' => [...$includes, 'Transportasi dan konsumsi selama umroh'],
                // The 13 January brochure prints the director's number (config site.director_whatsapp) and misprints "PPIU : 1033".
            ],
            'umroh-awal-ramadhan-12-hari-8-februari-2027' => [
                ...$umrah,
                'name' => 'Umroh Awal Ramadhan 12 Hari',
                'program_label' => 'Awal Ramadhan',
                'departure_date' => '2027-02-08',
                'hotel_makkah' => 'Nada Ajyad atau setaraf',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Taiba Hills atau setaraf',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia',
                'price_quad' => 44_500_000,
                'price_triple' => 45_500_000,
                'price_double' => 47_500_000,
                'includes' => [...$includes, 'Transportasi dan konsumsi selama umroh'],
            ],
            // "Keberangkatan akhir Juli 2027": no exact date yet, so it is listed by its note.
            'umroh-promo-milad-9-hari-juli-2027' => [
                ...$umrah,
                'name' => 'Umroh Promo Milad 9 Hari',
                'program_label' => 'Promo Milad',
                'departure_date' => null,
                'departure_note' => 'Akhir Juli 2027',
                'duration_days' => 9,
                'hotel_makkah' => 'Nada Ajyad atau setaraf',
                'hotel_makkah_stars' => 3,
                'hotel_madinah' => 'Taiba Hills atau setaraf',
                'hotel_madinah_stars' => 3,
                'airline' => 'Saudia',
                'price_quad' => 28_500_000,
                'price_triple' => 29_500_000,
                'price_double' => 31_500_000,
                'includes' => [...$includes, 'Transportasi dan konsumsi selama umroh'],
                'notes' => 'Tanggal pasti keberangkatan diumumkan menyusul. Harga sewaktu-waktu dapat berubah menyesuaikan kebijakan Kerajaan Arab Saudi dan maskapai.',
            ],
            'haji-plus-haji-khusus-2027' => [
                'type' => Package::HAJI_KHUSUS,
                'name' => 'Haji Plus (Haji Khusus) 2027',
                'program_label' => 'Harga musim haji 2027',
                'summary' => 'Paket haji khusus dengan nomor urut porsi resmi, hotel bintang 5 di Makkah dan Madinah, serta tenda Arafah-Mina di Zona A.',
                'notes' => 'Harga di atas adalah harga musim haji 2027 dan berlaku bagi jemaah yang berangkat pada musim haji 2027. Pendaftar baru berangkat sesuai nomor porsi (perkiraan antrean 6-7 tahun), dan harga paketnya mengikuti tahun keberangkatan tersebut.',
                'departure_note' => 'Mengikuti nomor porsi',
                'duration_days' => 25,
                'duration_approximate' => true,
                'hotel_makkah' => 'Zamzam Tower',
                'hotel_makkah_stars' => 5,
                'hotel_madinah' => 'Frontel Al Harithia',
                'hotel_madinah_stars' => 5,
                'accommodation_note' => 'Apartemen transit di sekitar kota Makkah',
                'airline' => 'Saudia',
                'route_departure' => 'Jakarta - Jeddah',
                'route_return' => 'Jeddah/Madinah - Jakarta',
                'currency' => 'USD',
                'price_quad' => 16_500,
                'price_triple' => 17_500,
                'price_double' => 19_500,
                'extra_costs' => [['label' => "Dam haji tamattu'/hadyu", 'amount' => 200]],
                'highlights' => [
                    'Nomor urut porsi resmi',
                    'Perkiraan daftar tunggu 6-7 tahun',
                    'Durasi haji sekitar 25 hari',
                    'Tenda Arafah-Mina 111-116 (Zona A)',
                ],
                'includes' => [
                    'Tiket pesawat kelas ekonomi pulang-pergi',
                    'Manasik dan bermalam di hotel Jakarta',
                    'Visa haji',
                    'Hotel sesuai paket',
                    'Perlengkapan',
                    'Transportasi dan konsumsi selama haji',
                ],
                'excludes' => [
                    'Transportasi dari luar Jakarta',
                    'Paspor, MCU, dan vaksin',
                    'Keperluan pribadi',
                    'Tur di luar program',
                ],
                'requirements' => [
                    'Usia minimal 12 tahun saat mendaftar.',
                    'Mengirimkan KTP, Kartu Keluarga, akta lahir atau akta nikah, dan paspor (bila ada) lewat WhatsApp atau email.',
                    // The brochure says "Setoran Awal $5000"; the official Bipih Khusus deposit is USD 4.000 (Kemenhaj, January 2026). Confirm the split with the client.
                    'Membayar setoran awal Bipih Khusus sesuai ketentuan pemerintah (USD 4.000 per Januari 2026) ke bank penerima setoran atas nama Menteri. Rincian biaya layanan kami dijelaskan tim kami sebelum Anda menyetor.',
                    'Pelunasan mengikuti tahun keberangkatan.',
                ],
                // "Estimasi Perjalanan Haji Plus" (brochure received 4 November 2026).
                'itinerary' => static::hajjItinerary(),
                'published_at' => now(),
            ],
        ];
    }

    /**
     * @return list<array{day: int, date: string, activity: string}>
     */
    private static function hajjItinerary(): array
    {
        $activities = [
            'Manasik di Jakarta',
            'Manasik di Jakarta',
            'Jakarta - Jeddah - Makkah',
            'Apartemen transit, tarwiyah',
            'Mina',
            'Wukuf di Arafah, Muzdalifah',
            'Muzdalifah - Mina',
            'Mina',
            'Mina',
            'Mina - apartemen transit',
            'Apartemen transit',
            'Makkah', 'Makkah', 'Makkah', 'Makkah', 'Makkah', 'Makkah',
            'Makkah - Madinah',
            'Madinah', 'Madinah', 'Madinah', 'Madinah', 'Madinah',
            'Madinah - Jakarta',
            'Tiba di Jakarta',
        ];

        return array_map(fn (string $activity, int $index): array => [
            'day' => $index + 1,
            'date' => ($index + 4).' Dzulhijjah',
            'activity' => $activity,
        ], $activities, array_keys($activities));
    }

    /**
     * Evergreen guides researched and fact-checked against official sources on
     * 3 November 2026. They are saved as drafts: a person must review them in
     * the admin panel (and fill in "Ditinjau oleh") before publishing.
     */
    private function seedDraftArticles(): void
    {
        $categories = collect([
            'legalitas-keamanan' => ['Legalitas & Keamanan', 'Cara memastikan travel umroh dan haji resmi.'],
            'panduan-umrah' => ['Panduan Umrah', 'Persiapan, dokumen, dan hal yang perlu diketahui calon jemaah.'],
            'biaya-paket' => ['Biaya & Paket', 'Memahami komponen biaya dan memilih paket.'],
        ])->map(fn (array $data, string $slug) => Category::firstOrCreate(['slug' => $slug], ['name' => $data[0], 'description' => $data[1]]));

        $articles = [
            'cara-cek-travel-umrah-dan-haji-resmi' => [
                'category' => 'legalitas-keamanan',
                'title' => 'Cara Cek Travel Umrah dan Haji Khusus Resmi Sebelum Membayar',
                'seo_title' => 'Cara Cek Izin Travel Umrah dan Haji Khusus Resmi',
                'excerpt' => 'Sebelum mendaftar umrah atau haji khusus, pastikan travelnya berizin resmi. Begini cara mengecek izin PPIU dan PIHK lewat aplikasi SatuHaji atau portal SISKOPATUH, mengenali tanda penipuan, dan membayar dengan aman.',
                'seo_description' => 'Cek izin PPIU/PIHK lewat SatuHaji atau SISKOPATUH, kenali tanda penipuan travel, dan bayar hanya ke rekening resmi sesuai aturan.',
            ],
            'syarat-dan-dokumen-umrah' => [
                'category' => 'panduan-umrah',
                'title' => 'Checklist Syarat dan Dokumen Umrah: Daftar Persiapan yang Mudah Dicentang',
                'seo_title' => 'Checklist Syarat dan Dokumen Umrah untuk Jemaah WNI',
                'excerpt' => 'Daftar centang persiapan umrah untuk WNI: paspor minimal 6 bulan, dokumen identitas, kartu JKN aktif, surat sehat, vaksin meningitis dan polio, dokumen dari travel, hingga hal yang perlu dicek sebelum membayar.',
                'seo_description' => 'Checklist syarat umrah untuk WNI: paspor, identitas, JKN aktif, surat sehat, vaksin meningitis dan polio, visa, dan cek izin travel sebelum membayar.',
            ],
            'memahami-biaya-paket-umrah' => [
                'category' => 'biaya-paket',
                'title' => 'Cara Membaca Harga Paket Umrah: Tipe Kamar, Rincian Biaya, dan Biaya Tambahan',
                'seo_title' => 'Cara Membaca Harga Paket Umrah dan Rincian Biayanya',
                'excerpt' => 'Harga paket umrah tidak cukup dilihat dari angkanya saja. Kenali arti harga per tipe kamar, komponen yang biasanya termasuk dan tidak termasuk, biaya tambahan seperti perlengkapan dan asuransi, serta alasan harga bisa berubah.',
                'seo_description' => 'Panduan membaca harga paket umrah: tipe kamar quad, triple, double, komponen termasuk dan tidak termasuk, biaya tambahan, dan alasan harga berubah.',
            ],
            'umrah-mandiri-atau-lewat-travel-resmi' => [
                'category' => 'legalitas-keamanan',
                'title' => 'Umrah Mandiri atau Lewat Travel Resmi? Perbandingan Jujur Menurut Aturan Terbaru',
                'seo_title' => 'Umrah Mandiri vs Travel Resmi (PPIU): Mana yang Tepat?',
                'excerpt' => 'Umrah mandiri kini sah menurut UU 14/2025, tetapi pelindungannya berbeda dengan umrah lewat PPIU berizin. Simak perbandingan jujur keduanya, syarat yang berlaku, risiko yang perlu dipahami, dan cara mengecek izin travel sebelum membayar.',
                'seo_description' => 'Umrah mandiri sah menurut UU 14/2025, tapi pelindungannya berbeda dengan lewat PPIU. Simak perbandingan, risiko, dan cara cek izin travel.',
            ],
        ];

        foreach ($articles as $slug => $article) {
            Article::firstOrCreate(['slug' => $slug], [
                'category_id' => $categories[$article['category']]->id,
                'title' => $article['title'],
                'seo_title' => $article['seo_title'],
                'excerpt' => $article['excerpt'],
                'seo_description' => $article['seo_description'],
                'body' => file_get_contents(__DIR__."/content/articles/{$slug}.md"),
                'author_name' => null, // a person's name, filled in by the client; empty means the company
                'published_at' => null,
            ]);
        }
    }
}
