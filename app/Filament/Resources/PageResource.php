<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Content CMS';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Page Details')->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        FileUpload::make('hero_image')
                            ->image()
                            ->directory('pages/hero')
                            ->columnSpanFull(),
                    ])->columns(2),

                    Section::make('Page Builder')->schema([
                        Builder::make('content')
                            ->blocks([
                                Builder\Block::make('hero_header')
                                    ->schema([
                                        TextInput::make('heading')->required(),
                                        TextInput::make('subheading'),
                                        FileUpload::make('image')->image()->directory('pages/blocks'),
                                        TextInput::make('cta_text')->label('CTA Button Text'),
                                        TextInput::make('cta_url')->label('CTA Link'),
                                    ]),
                                Builder\Block::make('rich_text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->required(),
                                    ]),
                                Builder\Block::make('cards_grid')
                                    ->schema([
                                        Forms\Components\Repeater::make('cards')
                                            ->schema([
                                                TextInput::make('title')->required(),
                                                Textarea::make('description'),
                                                FileUpload::make('image')->image(),
                                                TextInput::make('url')->label('Link URL'),
                                            ])
                                            ->grid(2),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Publishing')->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at'),
                    ]),
                    Section::make('SEO')->schema([
                        TextInput::make('seo_title'),
                        Textarea::make('seo_description'),
                    ]),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hero_image'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
