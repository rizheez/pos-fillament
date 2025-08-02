<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(onBlur: true),

                    TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true),

                    TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->live(onBlur: true)
                    ->mask(RawJs::make('$money($input)'))
    ->stripCharacters(','),

                    TextInput::make('sale_price')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->live(onBlur: true)
                    ->mask(RawJs::make('$money($input)'))
    ->stripCharacters(','),

                    TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')->label('Nama Produk')->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formatted_purchase_price')->label('Harga Beli')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('formatted_sale_price')->label('Harga Jual')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')->label('Stok')->sortable(),
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),


            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
