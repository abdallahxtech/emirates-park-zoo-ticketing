<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead Information')
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'abandoned_cart' => 'Abandoned Cart',
                                'inquiry' => 'General Inquiry',
                                'offline' => 'Offline Walk-in',
                            ])->default('inquiry'),
                        Forms\Components\TextInput::make('potential_value')->numeric()->prefix('AED'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'converted' => 'Converted',
                                'lost' => 'Lost',
                            ])
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Tracking Data (UTM)')
                    ->schema([
                        Forms\Components\TextInput::make('source'),
                        Forms\Components\TextInput::make('utm_source'),
                        Forms\Components\TextInput::make('utm_medium'),
                        Forms\Components\TextInput::make('utm_campaign'),
                        Forms\Components\TextInput::make('utm_content'),
                        Forms\Components\TextInput::make('utm_term'),
                        Forms\Components\TextInput::make('landing_page_url'),
                        Forms\Components\TextInput::make('ip_address'),
                    ])->columns(2)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Date'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'abandoned',
                        'warning' => 'new',
                        'info' => 'contacted',
                        'success' => 'converted',
                    ]),
                Tables\Columns\TextColumn::make('utm_source')->label('Source'),
                Tables\Columns\TextColumn::make('utm_campaign')->label('Campaign'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'converted' => 'Converted',
                        'abandoned' => 'Abandoned',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('convert')
                    ->action(fn(Lead $record) => $record->update(['status' => 'converted']))
                    ->visible(fn(Lead $record) => $record->status !== 'converted')
                    ->icon('heroicon-o-check'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
