<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nama kategori')
                    ->required()
                    ->maxLength(100),
                TextInput::make('slug')
                    ->label('Alamat kategori (slug)')
                    ->maxLength(120)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true)
                    ->helperText('Kosongkan agar dibuat otomatis dari nama. Jangan diubah setelah dipakai, karena tautan lama akan putus.'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->helperText('Tampil di halaman kategori dan dipakai Google.'),
            ]);
    }
}
