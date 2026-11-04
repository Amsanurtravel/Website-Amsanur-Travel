<?php

namespace App\Filament\Resources\Representatives\Tables;

use App\Models\Representative;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepresentativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Representative $record): ?string => $record->address)
                    ->wrap(),
                TextColumn::make('region')
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Tampil')
                    ->boolean(),
                TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada perwakilan daerah');
    }
}
