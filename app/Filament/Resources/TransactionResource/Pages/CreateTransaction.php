<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    // kurangi stok setelah transaksi dibuat
    protected function afterCreate(): void
    {
        $transaction = $this->getRecord();

        foreach ($transaction->transactionItems as $item) {
            $product = $item->product;

            if ($product) {
                $product->decrement('stock', $item->quantity);
            }
        }
    }
}
