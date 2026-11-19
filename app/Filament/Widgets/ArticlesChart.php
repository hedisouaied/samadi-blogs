<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\Article;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ArticlesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('Articles created (last 12 months)');
    }

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return Filament::auth()->user()?->hasPermission(Permission::ViewArticles) ?? false;
    }

    protected function getData(): array
    {
        $rows = Article::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (int) ($rows[$date->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Articles'),
                    'data' => $values,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
