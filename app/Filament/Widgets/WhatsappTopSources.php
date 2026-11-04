<?php

namespace App\Filament\Widgets;

use App\Models\WhatsappClick;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class WhatsappTopSources extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Asal klik WhatsApp')
            ->description('10 halaman dan paket dengan klik terbanyak dalam 30 hari terakhir.')
            ->records(fn (): array => $this->topSources())
            ->columns([
                TextColumn::make('source')
                    ->label('Halaman')
                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),
                TextColumn::make('package')
                    ->label('Paket')
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('clicks')
                    ->label('Klik')
                    ->numeric()
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada klik dalam 30 hari terakhir');
    }

    /**
     * Clicks grouped by page and package, most clicked first.
     *
     * @return list<array{__key: string, source: string, package: ?string, clicks: int}>
     */
    protected function topSources(): array
    {
        return WhatsappClick::query()
            ->with('package:id,name')
            ->where('created_at', '>=', today()->subDays(29))
            ->select('source', 'package_id')
            ->selectRaw('count(*) as clicks')
            ->groupBy('source', 'package_id')
            ->orderByDesc('clicks')
            ->orderBy('source')
            ->limit(10)
            ->get()
            ->map(fn (WhatsappClick $row): array => [
                '__key' => $row->source.'|'.($row->package_id ?? '-'),
                'source' => $row->source,
                'package' => $row->package?->name,
                'clicks' => (int) $row->clicks,
            ])
            ->all();
    }
}
