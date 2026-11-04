<?php

namespace App\Filament\Widgets;

use App\Models\WhatsappClick;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class WhatsappClicksOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Klik WhatsApp';

    protected ?string $description = 'Berapa kali tombol WhatsApp di situs diklik.';

    // Counts change slowly; refresh on page load only.
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Calendar days in the app timezone, today included.
        $weekStart = today()->subDays(6);
        $monthStart = today()->subDays(29);

        $counts = WhatsappClick::query()
            ->toBase()
            ->where('created_at', '>=', $monthStart)
            ->selectRaw('count(*) as month')
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as week', [$weekStart])
            ->first();

        $top = WhatsappClick::query()
            ->with('package:id,name')
            ->where('created_at', '>=', $monthStart)
            ->whereNotNull('package_id')
            ->select('package_id')
            ->selectRaw('count(*) as clicks')
            ->groupBy('package_id')
            ->orderByDesc('clicks')
            ->orderBy('package_id')
            ->first();

        return [
            Stat::make('7 hari terakhir', Number::format((int) $counts->week))
                ->description('Sejak '.$weekStart->translatedFormat('j M'))
                ->icon(Heroicon::OutlinedChatBubbleOvalLeft),
            Stat::make('30 hari terakhir', Number::format((int) $counts->month))
                ->description('Sejak '.$monthStart->translatedFormat('j M'))
                ->icon(Heroicon::OutlinedChatBubbleOvalLeft),
            Stat::make('Paket paling diminati (30 hari)', $top?->package?->name ?? '-')
                ->description($top ? Number::format((int) $top->clicks).' klik' : 'Belum ada klik dari halaman paket')
                ->icon(Heroicon::OutlinedTicket),
        ];
    }
}
