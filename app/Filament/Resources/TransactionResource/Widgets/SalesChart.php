<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        foreach (
            \App\Models\Transaction::selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
                ->groupBy('transaction_date')
                ->get() as $transaction
        ) {
            $labels[] = $transaction->date;
            $data[] = $transaction->total;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => $data,

                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',

                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
