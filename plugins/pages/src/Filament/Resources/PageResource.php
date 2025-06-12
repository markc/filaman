<?php

namespace FilaMan\Pages\Filament\Resources;

use FilaMan\Pages\Filament\Resources\PageResource\Pages;
use FilaMan\Pages\Models\Page;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\MarkdownEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Page Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9-]+$/'])
                            ->helperText('URL-friendly version of the title (lowercase, hyphens only)'),

                        Textarea::make('description')
                            ->maxLength(500)
                            ->rows(2),

                        Select::make('category')
                            ->options([
                                'Main' => 'Main',
                                'Company' => 'Company',
                                'Services' => 'Services',
                                'Documentation' => 'Documentation',
                                'Support' => 'Support',
                            ])
                            ->default('Main')
                            ->required(),

                        TextInput::make('order')
                            ->numeric()
                            ->default(999)
                            ->required(),

                        Toggle::make('published')
                            ->default(true),
                    ]),

                Section::make('Content')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->image()
                            ->directory('pages/images')
                            ->helperText('Optional featured image for the page'),

                        MarkdownEditor::make('content')
                            ->required()
                            ->helperText('Write your page content using GitHub Flavored Markdown'),
                    ]),

                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(60)
                            ->helperText('Recommended: 50-60 characters'),

                        Textarea::make('seo_description')
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText('Recommended: 150-160 characters'),

                        TextInput::make('og_image')
                            ->url()
                            ->helperText('URL to the Open Graph image'),
                    ]),

                Section::make('Advanced')
                    ->schema([
                        Textarea::make('custom_css')
                            ->rows(5)
                            ->helperText('Custom CSS for this page only'),

                        Textarea::make('custom_js')
                            ->rows(5)
                            ->helperText('Custom JavaScript for this page only'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('published')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Main' => 'Main',
                        'Company' => 'Company',
                        'Services' => 'Services',
                        'Documentation' => 'Documentation',
                        'Support' => 'Support',
                    ]),

                Tables\Filters\TernaryFilter::make('published'),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => "/pages/{$record->slug}")
                    ->openUrlInNewTab(),

                Actions\EditAction::make(),

                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
