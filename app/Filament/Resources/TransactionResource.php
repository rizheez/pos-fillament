<?php

namespace App\Filament\Resources;

use Filament\Forms;
use TextInput\Mask;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $products = Product::get();

        return $form
            ->schema([
                Section::make('Form Transaksi')
                    ->columns(1)
                    ->schema([
                        DatePicker::make('transaction_date')
                            ->default(now())
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->disabled(fn(Get $get) => count($get('transactionItems') ?? []) === 0),

                        Hidden::make('user_id')
                            ->default(auth()->id()),

                        Repeater::make('transactionItems')
                            ->label('Daftar Produk')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(
                                        $products->mapWithKeys(function ($product) {
                                            return [
                                                $product->id => sprintf('%s (Rp %s)', $product->name, number_format($product->sale_price, 0, ',', '.')),
                                            ];
                                        })
                                    )
                                    ->reactive()
                                    ->afterStateUpdated(
                                        fn($state, callable $set) =>
                                        $set('price', Product::find($state)?->sale_price ?? 0)
                                    )
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $set('subtotal', $get('price') * $get('quantity'));
                                        self::updateTotals($get, $set);
                                    })
                                    ->required(),

                                TextInput::make('price')
                                    ->label('Harga')

                                    ->readOnly()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrated(),

                                TextInput::make('quantity')
                                    ->label('Jumlah')

                                    ->default(1)
                                    ->disabled(fn(callable $get) => !$get('product_id'))
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $set('subtotal', $get('price') * $state);
                                        self::updateTotals($get, $set);
                                    }),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->mask(RawJs::make(<<<'JS'
        $input.replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    JS))
    ->stripCharacters(',')
                                    ->dehydrated(),
                            ])
                            ->defaultItems(1)
                            ->collapsible()
                            ->columns(2)
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                            ->deleteAction(
                                fn(Action $action) => $action->after(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                            )
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->columns(1)
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->afterStateHydrated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ])
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $items = collect($get('transactionItems'))
            ->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']) && !empty($item['price']));

        $total = $items->reduce(function ($carry, $item) {
            return $carry + ((float) $item['price'] * (int) $item['quantity']);
        }, 0);

        $set('total_amount', number_format($total, 2, '.', ''));
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //tanggal format indonesia contoh "01 Agustus 2025"
                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->date('d M Y')
                    ->formatStateUsing(function ($state) {
                        // Pastikan Carbon sudah di-import
                        \Carbon\Carbon::setLocale('id');
                        return \Carbon\Carbon::parse($state)->translatedFormat('d F Y');
                    })
                    ->sortable()
                    ->searchable(),

                //bikin namanya jadi hurup besar awal contoh "John Doe"
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->sortable()
                    ->searchable(),

                //show transaction items
                TextColumn::make('transactionItems')
                    ->label('Produk')
                    ->formatStateUsing(function ($record) {
                        return $record->transactionItems
                            ->map(fn($item) => $item->product?->name)
                            ->filter()
                            ->map(fn($name) => ucwords($name))
                            ->join(', ');
                    })
                    ->wrap() // Supaya tidak overflow jika produk banyak
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR', true) // Bisa sesuaikan formatnya
                    ->sortable(),



                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since(), // Menampilkan seperti "2 jam lalu"
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Dibuat Oleh')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                //filter dari tanggal
                Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->default(now()->subMonth()),

                        DatePicker::make('to')
                            ->label('Sampai Tanggal')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['from']) && isset($data['to'])) {
                            $query->whereBetween('transaction_date', [
                                $data['from'],
                                $data['to'],
                            ]);
                        }
                    }),

                Filter::make('total_amount')
                    ->form([
                        TextInput::make('min')
                            ->label('Minimum Total')
                            ->numeric()
                            ->default(0),

                        TextInput::make('max')
                            ->label('Maksimum Total')
                            ->numeric()
                            ->default(1000000),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['min']) && isset($data['max'])) {
                            $query->whereBetween('total_amount', [
                                $data['min'],
                                $data['max'],
                            ]);
                        }
                    }),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()
            ])
            ->headerActions([
                ExportAction::make(),
            ]);

    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('transactionItems', 'user');
    }
    public static function afterCreate(Model $record): void
    {
        foreach ($record->transactionItems as $item) {
            $product = $item->product;

            if ($product) {
                $product->decrement('stock', $item->quantity);
            }
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
