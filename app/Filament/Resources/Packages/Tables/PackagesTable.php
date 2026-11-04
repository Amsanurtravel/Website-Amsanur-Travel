<?php

namespace App\Filament\Resources\Packages\Tables;

use App\Filament\Resources\Packages\PackageResource;
use App\Models\Package;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Upcoming first (soonest on top), then undated packages, then past departures (latest first).
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw('case when departure_date is null then 1 when date(departure_date) < ? then 2 else 0 end', [today()->toDateString()])
                ->orderByRaw('case when date(departure_date) >= ? then departure_date end asc', [today()->toDateString()])
                ->orderBy('departure_date', 'desc'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama paket')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Package $record): ?string => $record->program_label),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Package::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => $state === Package::HAJI_KHUSUS ? 'info' : 'primary'),
                TextColumn::make('departure_date')
                    ->label('Berangkat')
                    ->date('j M Y')
                    ->placeholder(fn (Package $record): string => $record->departure_note ?: '-')
                    ->sortable(),
                TextColumn::make('lowest_price')
                    ->label('Mulai dari')
                    ->state(fn (Package $record): ?string => ($price = $record->lowestPrice()) !== null ? $record->money($price) : null)
                    ->placeholder('-'),
                TextColumn::make('seats_left')
                    ->label('Sisa kursi')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Penuh' : (string) $state)
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Package $record): string => $record->status === 'published' && $record->hasDeparted() ? 'departed' : $record->status)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Terbit',
                        'scheduled' => 'Terjadwal',
                        'departed' => 'Sudah berangkat',
                        default => 'Draf',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('updated_at')
                    ->label('Terakhir diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(Package::TYPES),
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
                SelectFilter::make('departure')
                    ->label('Keberangkatan')
                    ->options([
                        'upcoming' => 'Akan datang',
                        'past' => 'Sudah lewat',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'upcoming' => $query->upcoming(),
                        'past' => $query->whereDate('departure_date', '<', today()),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat di situs')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Package $record): string => route('packages.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Package $record): bool => $record->status === 'published'),
                ReplicateAction::make()
                    ->label('Duplikat')
                    ->modalHeading('Duplikat paket')
                    ->modalDescription('Salinan disimpan sebagai draf dengan alamat baru. Periksa harga dan sisa kursi sebelum menerbitkan.')
                    ->modalSubmitActionLabel('Duplikat')
                    ->excludeAttributes(['slug', 'published_at', 'seo_title', 'seo_description'])
                    // Start with an empty date so a copy never silently keeps the old departure.
                    ->mutateRecordDataUsing(fn (array $data): array => [...$data, 'departure_date' => null])
                    // The address is built from the date when the copy is saved, so ask for the new date first.
                    ->schema([
                        DatePicker::make('departure_date')
                            ->label('Tanggal keberangkatan salinan')
                            ->helperText('Kosongkan bila belum ada tanggal pasti.'),
                    ])
                    ->successNotificationTitle('Paket diduplikat sebagai draf')
                    ->successRedirectUrl(fn (Package $replica): string => PackageResource::getUrl('edit', ['record' => $replica])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada paket');
    }
}
