<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\LineChartWidget;

class OrdersChart extends LineChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Orders statistics';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected function getData(): array
    {
        $data = Trend::model(Order::class)
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orders count',
                    'data' => $data->map(fn (TrendValue $value): int => $value->aggregate)
                ]
            ],
            'labels' => $data->map(fn (TrendValue $value): string => $value->date)
        ];
    }
}
