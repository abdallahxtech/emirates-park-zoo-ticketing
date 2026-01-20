<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                $set('slug', \Str::slug($state))
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'general' => 'General Entry',
                                'vip' => 'VIP Entry',
                                'group' => 'Group Discount',
                                'child' => 'Child',
                                'senior' => 'Senior',
                                'student' => 'Student',
                            ])
                            ->required()
                            ->default('general'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('AED')
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('currency')
                            ->default('AED')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Capacity & Limits')
                    ->schema([
                        Forms\Components\TextInput::make('daily_capacity')
                            ->numeric()
                            ->label('Daily Capacity')
                            ->helperText('Leave empty for unlimited')
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('min_quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        
                        Forms\Components\TextInput::make('max_quantity')
                            ->required()
                            ->numeric()
                            ->default(10)
                            ->minValue(1),
                    ])->columns(3),

                Forms\Components\Section::make('Status & Display')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive tickets won\'t appear in availability'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Data')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->helperText('Age restrictions, special requirements, etc.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'general',
                        'success' => 'vip',
                        'info' => 'group',
                        'warning' => 'child',
                    ]),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('AED')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('daily_capacity')
                    ->label('Capacity')
                    ->default('Unlimited')
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'general' => 'General',
                        'vip' => 'VIP',
                        'group' => 'Group',
                        'child' => 'Child',
                        'senior' => 'Senior',
                        'student' => 'Student',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Ticket $record) {
                        $newTicket = $record->replicate();
                        $newTicket->name = $record->name . ' (Copy)';
                        $newTicket->slug = $record->slug . '-copy';
                        $newTicket->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Ticket duplicated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
