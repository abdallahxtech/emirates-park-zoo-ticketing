<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(), // Keys should be immutable once created to prevent code breakage, or create only
                
                Forms\Components\Select::make('type')
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'decimal' => 'Decimal',
                    ])
                    ->required()
                    ->live(), // React to type changes

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'boolean'),

                Forms\Components\Toggle::make('value_boolean')
                    ->label('Value')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                    ->dehydrated(false) // Don't save this field directly
                    ->afterStateHydrated(fn (Forms\Components\Toggle $component, $state, ?SystemSetting $record) => 
                        $component->state($record?->type === 'boolean' && filter_var($record->value, FILTER_VALIDATE_BOOLEAN))
                    )
                    ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('value', $state ? '1' : '0')),

                Forms\Components\Hidden::make('value')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean'),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                
                Forms\Components\Toggle::make('is_public')
                    ->label('Publicly Accessible via API'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Usually settings are seeded, preventing random key creation
    }
}
