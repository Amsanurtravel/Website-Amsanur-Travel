<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Models\Package;
use App\Support\WhatsApp;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paket')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->label('Jenis')
                            ->options(Package::TYPES)
                            ->default(Package::UMRAH)
                            ->selectablePlaceholder(false)
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama paket')
                            ->required()
                            ->maxLength(160),
                        TextInput::make('slug')
                            ->label('Alamat paket (slug)')
                            ->maxLength(160)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->validationMessages(['regex' => 'Gunakan huruf kecil, angka, dan tanda hubung saja, mis. umroh-desember-2026.'])
                            ->unique(ignoreRecord: true)
                            ->helperText('Kosongkan agar dibuat otomatis dari nama dan tanggal keberangkatan. Jangan diubah setelah terbit, karena tautan lama akan putus.'),
                        TextInput::make('program_label')
                            ->label('Label program')
                            ->maxLength(100)
                            ->helperText('Opsional, mis. "Awal Ramadhan".'),
                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->rows(2)
                            ->maxLength(300)
                            ->helperText('Tampil di kartu paket dan dipakai Google.'),
                    ]),

                Section::make('Jadwal')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        DatePicker::make('departure_date')
                            ->label('Tanggal keberangkatan'),
                        TextInput::make('departure_note')
                            ->label('Keterangan keberangkatan')
                            ->maxLength(200)
                            ->helperText('Dipakai bila belum ada tanggal pasti, mis. haji khusus "mengikuti nomor porsi".'),
                        TextInput::make('duration_days')
                            ->label('Lama perjalanan')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('hari'),
                        Toggle::make('duration_approximate')
                            ->label('Durasi perkiraan (±)')
                            ->inline(false)
                            ->default(false),
                    ]),

                Section::make('Hotel & penerbangan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('hotel_makkah')
                            ->label('Hotel Makkah')
                            ->maxLength(120),
                        Select::make('hotel_makkah_stars')
                            ->label('Bintang hotel Makkah')
                            ->options(self::starOptions()),
                        TextInput::make('hotel_madinah')
                            ->label('Hotel Madinah')
                            ->maxLength(120),
                        Select::make('hotel_madinah_stars')
                            ->label('Bintang hotel Madinah')
                            ->options(self::starOptions()),
                        TextInput::make('accommodation_note')
                            ->label('Catatan akomodasi')
                            ->maxLength(200)
                            ->columnSpanFull(),
                        TextInput::make('airline')
                            ->label('Maskapai')
                            ->maxLength(120),
                        TextInput::make('route_departure')
                            ->label('Rute berangkat')
                            ->maxLength(120)
                            ->placeholder('Jakarta - Jeddah'),
                        TextInput::make('route_return')
                            ->label('Rute pulang')
                            ->maxLength(120)
                            ->placeholder('Jeddah - Jakarta'),
                    ]),

                Section::make('Harga')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('currency')
                            ->label('Mata uang')
                            ->options(Package::CURRENCIES)
                            ->default('IDR')
                            ->selectablePlaceholder(false)
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('price_quad')
                            ->label('Quad')
                            ->integer()
                            ->minValue(0)
                            ->prefix(fn (Get $get): string => self::currencyPrefix($get('currency')))
                            ->helperText('Harga per orang, sekamar berempat.'),
                        TextInput::make('price_triple')
                            ->label('Triple')
                            ->integer()
                            ->minValue(0)
                            ->prefix(fn (Get $get): string => self::currencyPrefix($get('currency')))
                            ->helperText('Sekamar bertiga.'),
                        TextInput::make('price_double')
                            ->label('Double')
                            ->integer()
                            ->minValue(0)
                            ->prefix(fn (Get $get): string => self::currencyPrefix($get('currency')))
                            ->helperText('Sekamar berdua.'),
                        Repeater::make('extra_costs')
                            ->label('Biaya tambahan')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Keterangan')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('amount')
                                    ->label('Jumlah')
                                    ->integer()
                                    ->minValue(0)
                                    ->prefix(fn (Get $get): string => self::currencyPrefix($get('../../currency'))),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Tambah biaya')
                            ->helperText('Biaya di luar harga paket, mis. Perlengkapan dan asuransi.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Fasilitas')
                    ->columnSpanFull()
                    ->schema([
                        self::listField('highlights', 'Keunggulan program'),
                        self::listField('includes', 'Sudah termasuk'),
                        self::listField('excludes', 'Tidak termasuk'),
                        self::listField('requirements', 'Persyaratan pendaftaran'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->helperText('mis. "Harga dapat berubah mengikuti kebijakan Saudi dan maskapai."'),
                    ]),

                Section::make('Jadwal perjalanan')
                    ->columnSpanFull()
                    ->description('Opsional. Hari berurutan dengan kegiatan yang sama digabung otomatis di situs, mis. "12–17 Makkah".')
                    ->collapsed()
                    ->schema([
                        Repeater::make('itinerary')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('day')
                                    ->label('Hari ke')
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(60)
                                    ->required(),
                                TextInput::make('date')
                                    ->label('Tanggal')
                                    ->maxLength(40)
                                    ->placeholder('mis. 4 Dzulhijjah'),
                                TextInput::make('activity')
                                    ->label('Kegiatan')
                                    ->required()
                                    ->maxLength(120),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->reorderable()
                            ->addActionLabel('Tambah hari'),
                    ]),

                Section::make('Pembimbing & kuota')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('mentor_id')
                            ->label('Pembimbing ibadah')
                            ->relationship('mentor', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('affiliation')
                                    ->label('Asal lembaga')
                                    ->maxLength(200),
                            ]),
                        TextInput::make('seats_left')
                            ->label('Sisa kursi')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->helperText('Kosongkan bila tidak ingin ditampilkan. Isi 0 bila penuh.'),
                        TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp khusus')
                            ->tel()
                            ->maxLength(30)
                            // A typo here would send every enquiry for this departure to a stranger, so check it.
                            ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                if (filled($value) && ! WhatsApp::isValid((string) $value)) {
                                    $fail('Nomor WhatsApp tidak valid. Contoh: 0812-3456-7890.');
                                }
                            })
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? WhatsApp::number($state) : null)
                            ->helperText('Kosongkan untuk memakai nomor utama situs. Isi hanya nomor resmi perusahaan yang juga tercantum di brosur atau situs.'),
                    ]),

                Section::make('Penerbitan')
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Waktu terbit')
                            ->seconds(false)
                            ->helperText('Kosongkan untuk menyimpan sebagai draf. Isi tanggal di masa depan untuk menjadwalkan.'),
                    ]),

                Section::make('SEO (opsional)')
                    ->columnSpanFull()
                    ->description('Kosongkan untuk memakai nama, tanggal, dan ringkasan paket.')
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

    /**
     * A list of short lines, stored as a flat JSON array of strings.
     */
    private static function listField(string $name, string $label): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->simple(
                TextInput::make('item')
                    ->label($label)
                    ->required()
                    ->maxLength(200),
            )
            ->defaultItems(0)
            ->reorderable()
            ->addActionLabel('Tambah baris');
    }

    /**
     * @return array<int, string>
     */
    private static function starOptions(): array
    {
        return collect(range(1, 5))
            ->mapWithKeys(fn (int $stars): array => [$stars => str_repeat('★', $stars)])
            ->all();
    }

    private static function currencyPrefix(?string $currency): string
    {
        return $currency === 'USD' ? 'USD' : 'Rp';
    }
}
