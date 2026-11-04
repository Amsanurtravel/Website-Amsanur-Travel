<?php

namespace App\Filament\Resources\Mentors\Tables;

use App\Models\Mentor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MentorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('packages'))
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Mentor $record): ?string => $record->role),
                TextColumn::make('affiliation')
                    ->label('Asal lembaga')
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('packages_count')
                    ->label('Jumlah paket')
                    ->sortable(),
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
            ->emptyStateHeading('Belum ada pembimbing');
    }
}
