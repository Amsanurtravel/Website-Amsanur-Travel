<?php

namespace App\Filament\Resources\Mentors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pembimbing ibadah')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Tulis lengkap dengan gelar, mis. "KH. Ahmad Fulan, Lc."'),
                        TextInput::make('role')
                            ->label('Peran')
                            ->required()
                            ->maxLength(120)
                            ->default('Pembimbing Ibadah'),
                        TextInput::make('affiliation')
                            ->label('Asal lembaga')
                            ->maxLength(200)
                            ->helperText('mis. pesantren, masjid, atau ormas tempat beliau mengajar.'),
                        Textarea::make('bio')
                            ->label('Profil singkat')
                            ->rows(4)
                            ->helperText('Tampil di kartu pembimbing: beranda, Tentang Kami, dan halaman paket.'),
                        Textarea::make('profile')
                            ->label('Profil lengkap')
                            ->rows(12)
                            ->helperText('Tampil di halaman Pembimbing yang terbuka saat kartu pembimbing diklik. Pisahkan paragraf dengan satu baris kosong.'),
                    ]),

                Section::make('Foto')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            // JPEG, PNG or WebP only: an SVG could carry a script on the site's own domain.
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('mentors')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('800')
                            ->automaticallyResizeImagesToHeight('1000')
                            ->automaticallyUpscaleImagesWhenResizing(false)
                            ->helperText('JPG, PNG, atau WebP, sebaiknya foto tegak (potret). Foto dari ponsel boleh langsung diunggah; diperkecil otomatis ke maks. 800 × 1000 px (maks. 10 MB).'),
                    ]),

                Section::make('Tampilan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Angka kecil tampil lebih dulu.'),
                        Toggle::make('is_active')
                            ->label('Tampilkan di situs')
                            ->inline(false)
                            ->default(true),
                    ]),
            ]);
    }
}
