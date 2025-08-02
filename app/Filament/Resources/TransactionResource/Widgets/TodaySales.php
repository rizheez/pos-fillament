<?php

namespace App\Filament\Resources\TransactionResource\Widgets;


use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodaySales extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $today = now()->toDateString();

        return [
            Stat::make('Total Transaksi Hari Ini', Transaction::whereDate('transaction_date', $today)->count()),
            Stat::make('Total Pendapatan Hari Ini', 'Rp ' . number_format(Transaction::whereDate('transaction_date', $today)->sum('total_amount'), 0, ',', '.')),
            //total semua transaksi
            Stat::make('Total Transaksi', Transaction::count())->color('success')
                ->description('Total transaksi yang telah dilakukan'),
            Stat::make('Total Pendapatan', 'Rp ' . number_format(Transaction::sum('total_amount'), 0, ',', '.'))
                ->color('success')
                ->description('Total pendapatan dari semua transaksi')
                ->chart(Transaction::selectRaw('SUM(total_amount) as total')
                    ->groupBy('transaction_date')
                    ->pluck('total')
                    ->toArray()),
        ];
    }
}
