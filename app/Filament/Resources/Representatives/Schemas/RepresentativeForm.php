<?php

namespace App\Filament\Resources\Representatives\Schemas;

use App\Models\Representative;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RepresentativeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Perwakilan daerah')
                    ->description('Tampil di halaman Perwakilan, dikelompokkan per wilayah. Matikan "Tampilkan di situs" segera bila seseorang tidak lagi menjadi perwakilan.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('region')
                            ->label('Wilayah')
                            ->required()
                            ->maxLength(80)
                            ->datalist(fn (): array => Representative::query()->distinct()->orderBy('region')->pluck('region')->all())
                            ->helperText('Tulis sama persis untuk perwakilan di wilayah yang sama, mis. "Jawa Timur".'),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(160)
                            ->helperText('Nama perorangan dengan gelar, atau nama lembaga (mis. KBIH) beserta penanggung jawabnya.'),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(2)
                            ->maxLength(300),
                        TextInput::make('phone')
                            ->label('Telepon atau WhatsApp')
                            ->tel()
                            ->maxLength(30)
                            ->helperText('Opsional. Kosongkan bila perwakilan tidak ingin nomornya tampil.'),
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
                            ->helperText('Angka kecil tampil lebih dulu. Wilayah diurutkan menurut perwakilan pertamanya.'),
                        Toggle::make('is_active')
                            ->label('Tampilkan di situs')
                            ->inline(false)
                            ->default(true),
                    ]),
            ]);
    }
}
