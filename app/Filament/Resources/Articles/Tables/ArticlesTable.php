<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Terbit',
                        'scheduled' => 'Terjadwal',
                        default => 'Draf',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->label('Waktu terbit')
                    ->dateTime('j M Y, H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Terakhir diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draf',
                        'scheduled' => 'Terjadwal',
                        'published' => 'Terbit',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'draft' => $query->whereNull('published_at'),
                        'scheduled' => $query->where('published_at', '>', now()),
                        'published' => $query->published(),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat di situs')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Article $record): string => route('articles.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->status === 'published'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada artikel');
    }
}
