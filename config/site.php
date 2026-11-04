<?php

/*
|--------------------------------------------------------------------------
| Company profile shown on the public site
|--------------------------------------------------------------------------
|
| Sources: the client's brochures, licence decrees, company profile (2024)
| and contact details sent on 4 November 2026, unless noted. Items marked
| "confirm" still need the client's confirmation. Empty values hide the
| matching block on the site.
|
*/

return [

    'brand' => 'Amsa Nur Tour & Travel',

    'legal_name' => 'PT Amsa Nur Indah Mandiri',

    'tagline' => 'Kami tidak sekadar mengantar, kami menjadi bagian dari ibadah Anda.',

    // Company profile 2024: founded in Jakarta in 2002 (deed No. 39, 19 June 2002).
    'founded' => 2002,

    'director' => 'Drs. H. M. Aminullah Tsamud',

    // First licences, from the company profile ("Izin Penyelenggaraan Umroh No. D/394 tahun 2002", "Izin Penyelenggaraan Haji No. D/222 tahun 2004").
    'licensed_since' => [
        'PPIU' => 2002,
        'PIHK' => 2004,
    ],

    /*
    |--------------------------------------------------------------------------
    | Official WhatsApp number
    |--------------------------------------------------------------------------
    |
    | Either 0812... or 62812... works. Every WhatsApp button goes through
    | /wa (see WhatsappController) so clicks per page and package are counted.
    | The buttons are hidden while this is empty.
    |
    */

    'whatsapp' => env('SITE_WHATSAPP', '082112220576'),

    'whatsapp_message' => env('SITE_WHATSAPP_MESSAGE', "Assalamu'alaikum, saya ingin bertanya tentang paket umroh/haji Amsa Nur."),

    // The director's own WhatsApp, printed on some brochures (13 January, 16 December).
    // Confirmed by the client on 4 November 2026. Listed on the legal page so visitors
    // can verify it; the WhatsApp buttons still use 'whatsapp' above.
    'director_whatsapp' => env('SITE_DIRECTOR_WHATSAPP', '0812-1319-0005'),

    // Shown as "Kantor Pusat" on the brochures.
    'phone' => env('SITE_PHONE', '0821-1222-0576'),

    // Confirmed by the client on 4 November 2026; also the one in SISKOPATUH.
    'email' => env('SITE_EMAIL', 'amsanur99@gmail.com'),

    /*
    |--------------------------------------------------------------------------
    | Operating licences
    |--------------------------------------------------------------------------
    |
    | Numbers and dates from the decrees the client sent (KMA 1031/2019 and
    | KMA 147/2020). Accreditation from the public SISKOPATUH registry, checked
    | 3 November 2026: both licences ACTIVE and not blocked.
    |
    | The registry still lists the PIHK decree as "KMA Nomor 839 Tahun 2016".
    | KMA 147/2020 (Diktum KEEMPAT) revokes that decree, so 147/2020 is the
    | current one and registry_note explains the difference to visitors.
    | Remove the note once the registry shows 147/2020.
    |
    */

    'permits' => [
        [
            'code' => 'PPIU',
            'name' => 'Penyelenggara Perjalanan Ibadah Umrah',
            'number' => '1031 Tahun 2019',
            'short' => 'PPIU 1031/2019',
            'date' => '25 November 2019',
            'accreditation' => 'B (82,54), berlaku 27 Februari 2025 sampai 26 Februari 2030',
            'registry_note' => null,
        ],
        [
            'code' => 'PIHK',
            'name' => 'Penyelenggara Ibadah Haji Khusus',
            'number' => env('SITE_PIHK_NUMBER', '147 Tahun 2020'),
            'short' => env('SITE_PIHK_SHORT', 'PIHK 147/2020'),
            'date' => '28 Februari 2020',
            'accreditation' => 'B (81,60), berlaku 27 Februari 2025 sampai 26 Februari 2030',
            'registry_note' => 'Di SISKOPATUH, izin PIHK kami masih tercatat dengan SK sebelumnya, KMA Nomor 839 Tahun 2016. SK Nomor 147 Tahun 2020 mencabut dan menggantikan SK tersebut.',
        ],
    ],

    // Licences were issued by the Ministry of Religious Affairs; since UU 14/2025 the Ministry of Hajj and Umrah supervises them.
    'permit_issuer' => 'Kementerian Agama Republik Indonesia',

    'permit_supervisor' => 'Kementerian Haji dan Umrah Republik Indonesia',

    // When the status and accreditation above were last compared with SISKOPATUH. Update after each check.
    'permits_checked_at' => '3 November 2026',

    /*
    |--------------------------------------------------------------------------
    | Company registration and memberships (company profile 2024)
    |--------------------------------------------------------------------------
    |
    | Memberships as listed in the profile; the client confirmed on 4 November
    | 2026 that all three are still active.
    |
    */

    'company_documents' => [
        ['label' => 'Akta pendirian', 'value' => 'Akta Notaris H. Rizal Sudarmadi, SH. Nomor 39, 19 Juni 2002'],
        ['label' => 'Pengesahan badan hukum', 'value' => 'Keputusan Menteri Kehakiman Nomor C-12492 HT.01.01.TH.2002, 9 Juli 2002'],
        ['label' => 'Perubahan data perusahaan terakhir', 'value' => 'Nomor AHU-06394.40.22.2014, 5 Mei 2014'],
    ],

    'memberships' => [
        ['code' => 'KESTHURI', 'name' => 'Kesatuan Tour Travel Haji Umrah Republik Indonesia', 'number' => '11/Kesthuri/2014'],
        ['code' => 'ASITA', 'name' => 'Asosiasi Perusahaan Perjalanan Indonesia', 'number' => 'NIA 0919/VIII/DPP/2004'],
        ['code' => 'KADIN', 'name' => 'Kamar Dagang dan Industri Indonesia', 'number' => '20201-11092075/4-1-2011'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payments: only to accounts in the company's name
    |--------------------------------------------------------------------------
    |
    | From the company profile and the client's message (4 November 2026).
    |
    */

    'bank_accounts' => [
        [
            'bank' => 'Bank Mandiri',
            'currency' => 'Rupiah',
            'number' => '006-000-7533296',
            'holder' => 'PT Amsa Nur Indah Mandiri',
        ],
        [
            'bank' => 'Bank Syariah Indonesia (BSI)',
            'currency' => 'Dolar AS (USD)',
            'number' => '708-544-6791',
            'holder' => 'PT Amsa Nur Indah Mandiri',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Office
    |--------------------------------------------------------------------------
    |
    | Confirmed by the client on 4 November 2026 (same as SISKOPATUH). The
    | company owns the office building. The map link is hidden while empty.
    |
    */

    'office' => [
        'name' => 'Kantor Pusat',
        'street' => $street = env('SITE_ADDRESS_STREET', 'Jl. Pramuka Raya No. 390 A, RT 010/RW 06, Kel. Paseban, Kec. Senen'),
        'locality' => $locality = env('SITE_ADDRESS_LOCALITY', 'Jakarta Pusat'),
        'region' => $region = env('SITE_ADDRESS_REGION', 'DKI Jakarta'),
        'postal_code' => $postalCode = env('SITE_POSTAL_CODE', '10440'),
        // One line for the footer and pages, built from the parts above.
        'address' => $street ? implode(', ', array_filter([$street, $locality, trim($region.' '.$postalCode)])) : null,
        'phone' => env('SITE_OFFICE_PHONE', '(021) 3106932'),
        'maps_url' => env('SITE_MAPS_URL'),
        // 'opens'/'closes' (24h) feed the structured data; null on closed days.
        'hours' => [
            ['days' => 'Senin–Jumat', 'day_codes' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '09:00', 'closes' => '17:00'],
            ['days' => 'Sabtu', 'day_codes' => ['Saturday'], 'opens' => '09:00', 'closes' => '12:00'],
            ['days' => 'Ahad dan tanggal merah', 'day_codes' => [], 'opens' => null, 'closes' => null],
        ],
    ],

    // Kemenhaj complaint channels, for the "if something is wrong" step.
    'complaints' => [
        'hotline' => '0811-888-5888',
        'email' => 'pengaduan@haji.go.id',
    ],

    // Google Business Profile reviews link. Hidden while empty.
    'reviews_url' => env('SITE_REVIEWS_URL'),

    'socials' => array_filter([
        'Instagram' => env('SITE_INSTAGRAM', 'https://www.instagram.com/amsanurtourtravel/'),
        'TikTok' => env('SITE_TIKTOK', 'https://www.tiktok.com/@amsanurtravel'),
        'Facebook' => env('SITE_FACEBOOK'),
        'YouTube' => env('SITE_YOUTUBE'),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Pilgrim photos
    |--------------------------------------------------------------------------
    |
    | From past trips, taken from the company profile (2024); the client agreed
    | on 4 November 2026 that they may be shown. Files live in
    | public/images/jemaah as {file}-480.webp and {file}-960.webp (3:2);
    | 'width'/'height' describe the larger one. The home page shows the first
    | four, "Tentang Kami" all of them.
    |
    */

    'gallery' => [
        ['file' => 'ziarah-madinah', 'width' => 960, 'height' => 640, 'caption' => 'Ziarah di sekitar kota Madinah', 'alt' => 'Rombongan jemaah membentangkan spanduk Jamaah Umroh PT Amsa Nur Indah Mandiri di depan sebuah masjid di Madinah'],
        ['file' => 'ibadah-makkah', 'width' => 960, 'height' => 640, 'caption' => 'Beribadah di Makkah', 'alt' => 'Jemaah Amsa Nur berpakaian ihram saat beribadah di Makkah'],
        ['file' => 'manasik-jakarta', 'width' => 960, 'height' => 640, 'caption' => 'Manasik di Jakarta sebelum berangkat', 'alt' => 'Jemaah mengikuti manasik umroh di aula hotel di Jakarta'],
        ['file' => 'masjid-nabawi', 'width' => 960, 'height' => 640, 'caption' => 'Beribadah di Madinah (Raudhah)', 'alt' => 'Jemaah pria Amsa Nur berfoto bersama di dalam Masjid Nabawi'],
        ['file' => 'ziarah-makkah', 'width' => 960, 'height' => 640, 'caption' => 'Ziarah di sekitar kota Makkah', 'alt' => 'Rombongan jemaah dengan spanduk Amsa Nur di kaki bukit saat ziarah di sekitar Makkah'],
        ['file' => 'bandara', 'width' => 864, 'height' => 576, 'caption' => 'Hari pertama: Jakarta–Jeddah–Makkah', 'alt' => 'Rombongan jemaah berfoto dengan spanduk Selamat Datang Jamaah Umroh di bandara'],
        ['file' => 'qiyamul-lail', 'width' => 960, 'height' => 640, 'caption' => 'Pelatihan qiyamul lail di Makkah', 'alt' => 'Jemaah salat berjamaah saat pelatihan qiyamul lail'],
        ['file' => 'pelataran-nabawi', 'width' => 960, 'height' => 640, 'caption' => 'Beribadah di Madinah', 'alt' => 'Jemaah duduk di pelataran Masjid Nabawi pada malam hari'],
    ],

    // Google Analytics 4 measurement ID (G-...). No analytics script while empty.
    'ga4_id' => env('SITE_GA4_ID'),

    // Address of the static copy for the client preview on Vercel (php artisan site:export).
    'preview_url' => env('SITE_PREVIEW_URL', 'https://websiteamsanurtravel.vercel.app'),

];
