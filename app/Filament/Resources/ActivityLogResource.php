<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Audit Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')
                    ->label('Action Type'),
                Forms\Components\TextInput::make('user_name')
                    ->label('User'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Changes')
                    ->schema([
                        Forms\Components\KeyValue::make('old_values')
                            ->label('Old Values'),
                        Forms\Components\KeyValue::make('new_values')
                            ->label('New Values'),
                    ])->columns(2),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address'),
                Forms\Components\TextInput::make('user_agent')
                    ->label('User Agent'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Time'),
                Tables\Columns\TextColumn::make('user_name')
                    ->searchable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['created', 'confirmed', 'completed']),
                        'warning' => fn ($state) => in_array($state, ['updated', 'rescheduled']),
                        'danger' => fn ($state) => in_array($state, ['deleted', 'cancelled', 'failed']),
                    ]),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
}
