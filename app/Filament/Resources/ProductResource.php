<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('base_price')
                            ->required()
                            ->numeric()
                            ->prefix('AED'),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('slug')->required(),
                            ]),
                        Forms\Components\TextInput::make('galaxy_id')
                            ->label('Galaxy Ticket ID')
                            ->helperText('External ID for Galaxy Ticketing System mapping'),
                    ])->columns(2),

                Forms\Components\Section::make('Capacity Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('has_capacity')
                            ->label('Enforce Capacity Limit')
                            ->live(),
                        Forms\Components\TextInput::make('capacity_per_day')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('has_capacity')),
                        Forms\Components\Toggle::make('is_time_slotted')
                            ->label('Has Time Slots'),
                        
                        Forms\Components\KeyValue::make('options_config')
                            ->label('VIP Options Configuration')
                            ->helperText('Define available food types or other options. Key=food_types, Value=International, Arabic, etc.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('AED')
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_capacity')
                    ->boolean(),
                Tables\Columns\TextColumn::make('capacity_per_day')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            // RelationManagers for PricingRules can go here later
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
