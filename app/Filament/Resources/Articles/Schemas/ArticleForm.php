<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi artikel')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(160),
                        TextInput::make('slug')
                            ->label('Alamat artikel (slug)')
                            ->maxLength(160)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->validationMessages(['regex' => 'Gunakan huruf kecil, angka, dan tanda hubung saja, mis. cara-cek-izin-travel.'])
                            ->unique(ignoreRecord: true)
                            ->helperText('Kosongkan agar dibuat otomatis dari judul. Jangan diubah setelah terbit, karena tautan lama akan putus.'),
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(2)
                            ->maxLength(300)
                            ->helperText('Tampil di kartu pada daftar artikel.'),
                        // No file attachments: body images would bypass the cover's size limits until an image pipeline exists.
                        MarkdownEditor::make('body')
                            ->label('Isi')
                            ->required()
                            ->minHeight('24rem')
                            ->toolbarButtons([
                                ['bold', 'italic', 'strike', 'link'],
                                ['heading', 'blockquote', 'bulletList', 'orderedList', 'table'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Tulis dengan Markdown. Kode HTML tidak diizinkan dan akan dibuang.'),
                    ]),

                Section::make('Gambar sampul')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('cover_path')
                            ->label('Gambar sampul')
                            ->image()
                            // JPEG, PNG or WebP only: an SVG could carry a script on the site's own domain.
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('articles')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('1600')
                            ->automaticallyResizeImagesToHeight('1600')
                            ->automaticallyUpscaleImagesWhenResizing(false)
                            ->live()
                            ->helperText('JPG, PNG, atau WebP. Foto dari ponsel boleh langsung diunggah; diperkecil otomatis ke 1600 px (maks. 10 MB).'),
                        TextInput::make('cover_alt')
                            ->label('Teks alternatif gambar')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => filled($get('cover_path')))
                            ->helperText('Jelaskan isi gambar dalam satu kalimat, untuk pembaca layar dan Google.'),
                    ]),

                Section::make('Penerbitan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama kategori')
                                    ->required()
                                    ->maxLength(100),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2),
                            ]),
                        DateTimePicker::make('published_at')
                            ->label('Waktu terbit')
                            ->seconds(false)
                            ->helperText('Kosongkan untuk menyimpan sebagai draf. Isi tanggal di masa depan untuk menjadwalkan.'),
                        TextInput::make('author_name')
                            ->label('Penulis')
                            ->maxLength(100)
                            ->helperText('Nama orang. Kosongkan bila ditulis atas nama perusahaan.'),
                        TextInput::make('reviewer_name')
                            ->label('Ditinjau oleh')
                            ->maxLength(100)
                            ->helperText('Disarankan untuk artikel tentang aturan, izin, dan biaya.'),
                    ]),

                Section::make('SEO (opsional)')
                    ->columnSpanFull()
                    ->description('Kosongkan untuk memakai judul dan ringkasan artikel.')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Judul untuk Google')
                            ->maxLength(70),
                        Textarea::make('seo_description')
                            ->label('Deskripsi untuk Google')
                            ->rows(2)
                            ->maxLength(170),
                    ]),
            ]);
    }
}
