<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Product extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', \App\Models\Product::count())
                ->description('Total number of products available')
                ->color('primary')
                ->icon('heroicon-s-archive-box-arrow-down'),
            Stat::make('Total Categories', \App\Models\Category::count())
                ->description('Total number of product categories')
                ->color('secondary')
                ->icon('heroicon-s-folder-open'),


            Stat::make('Total Stock', \App\Models\Product::sum('stock'))
                ->description('Total stock available across all products')
                ->color('success')
                ->icon('heroicon-s-chart-bar-square')
        ];
    }
}
