<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Testimoni')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama jemaah')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(100),
                        TextInput::make('trip')
                            ->label('Perjalanan')
                            ->maxLength(120)
                            ->helperText('mis. "Umroh Desember 2025".'),
                        Select::make('rating')
                            ->label('Penilaian')
                            ->options(collect(range(1, 5))->mapWithKeys(fn (int $stars): array => [$stars => str_repeat('★', $stars)])->all())
                            ->placeholder('Tanpa penilaian'),
                        Textarea::make('quote')
                            ->label('Isi testimoni')
                            ->required()
                            ->rows(4)
                            ->maxLength(600)
                            ->columnSpanFull(),
                    ]),

                Section::make('Penerbitan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Terbitkan')
                            ->inline(false)
                            ->default(false)
                            ->helperText('Terbitkan hanya testimoni asli dengan izin jemaah.'),
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Angka kecil tampil lebih dulu.'),
                    ]),
            ]);
    }
}
