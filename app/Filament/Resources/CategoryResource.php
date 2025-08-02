<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Form Kategori')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn(string $state, callable $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->maxLength(100),


                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Deskripsi kategori ini akan ditampilkan di halaman produk.')
                        ->columnSpan('full'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Kategori')->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->searchable()->sortable()->limit(50),
            ])
            ->filters([
                //


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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
