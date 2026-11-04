<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Sample articles for local development only (see DatabaseSeeder). The text is
 * placeholder content, not real guidance about umrah or hajj. Slugs carry a
 * "contoh-" prefix so re-seeding can never overwrite a real article.
 */
class ArticleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = collect([
            'legalitas-keamanan' => ['Legalitas & Keamanan', 'Cara memastikan travel umrah dan haji resmi.'],
            'panduan-umrah' => ['Panduan Umrah', 'Persiapan, dokumen, dan hal yang perlu diketahui calon jemaah.'],
            'biaya-paket' => ['Biaya & Paket', 'Memahami komponen biaya dan memilih paket.'],
            'kabar-amsa-nur' => ['Kabar Amsa Nur', 'Kegiatan manasik dan keberangkatan jemaah.'],
        ])->map(fn (array $data, string $slug) => Category::updateOrCreate(
            ['slug' => $slug],
            ['name' => $data[0], 'description' => $data[1]],
        ));

        // slug => [category, title, days since published (null = draft)]
        $articles = [
            'contoh-cara-memeriksa-travel-umrah-resmi' => ['legalitas-keamanan', '[Contoh] Cara memeriksa travel umrah resmi', 1],
            'contoh-dokumen-sebelum-berangkat' => ['panduan-umrah', '[Contoh] Dokumen yang perlu disiapkan sebelum berangkat', 4],
            'contoh-komponen-biaya-paket' => ['biaya-paket', '[Contoh] Apa saja yang termasuk dalam biaya paket', 12],
            'contoh-umrah-mandiri-atau-travel' => ['legalitas-keamanan', '[Contoh] Umrah mandiri atau lewat travel resmi', 25],
            'contoh-laporan-manasik' => ['kabar-amsa-nur', '[Contoh] Laporan manasik jemaah', 40],
            'contoh-draf-belum-terbit' => ['panduan-umrah', '[Contoh] Draf yang belum terbit', null],
        ];

        foreach ($articles as $slug => [$category, $title, $daysAgo]) {
            Article::updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'excerpt' => 'Ringkasan contoh untuk melihat tata letak kartu artikel di daftar.',
                'body' => $this->sampleBody($title),
                'author_name' => 'Tim Amsa Nur',
                'reviewer_name' => 'Nama Peninjau',
                'published_at' => $daysAgo === null ? null : now()->subDays($daysAgo),
            ]);
        }
    }

    private function sampleBody(string $title): string
    {
        return <<<MD
Ini teks contoh untuk melihat tata letak artikel "{$title}". Isinya bukan panduan sebenarnya; ganti dengan tulisan yang sudah diperiksa.

## Subjudul pertama

Paragraf kedua memperlihatkan jarak antarparagraf dan **teks tebal**, serta [tautan ke daftar artikel](/artikel).

- Poin pertama
- Poin kedua
- Poin ketiga

### Subjudul tingkat tiga

> Kutipan contoh untuk melihat gaya blockquote.

| Kolom A | Kolom B |
| --- | --- |
| Isi 1 | Isi 2 |
| Isi 3 | Isi 4 |
MD;
    }
}
