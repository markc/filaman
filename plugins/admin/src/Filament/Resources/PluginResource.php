<?php

namespace FilaMan\Admin\Filament\Resources;

use FilaMan\Admin\Services\PluginManager;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PluginResource extends Resource
{
    protected static ?string $model = \FilaMan\Admin\Models\Plugin::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Plugin Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->disabled()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('display_name')
                            ->label('Display Name')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('version')
                            ->disabled()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('author')
                            ->disabled()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('url')
                            ->label('Plugin URL')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Enabled')
                            ->helperText('Enable or disable this plugin'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Plugin Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Configuration')
                            ->helperText('Plugin-specific configuration options')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Plugin')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Model $record): string => $record->name),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('enabled')
                    ->boolean()
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('author')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Enabled')
                    ->falseLabel('Disabled')
                    ->queries(
                        true: fn ($query) => $query->where('enabled', true),
                        false: fn ($query) => $query->where('enabled', false),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle')
                    ->label(fn (Model $record): string => $record->enabled ? 'Disable' : 'Enable')
                    ->icon(fn (Model $record): string => $record->enabled ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Model $record): string => $record->enabled ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $pluginManager = app(PluginManager::class);

                        if ($record->enabled) {
                            $success = $pluginManager->disablePlugin($record->name);
                            $message = $success ? 'Plugin disabled successfully' : 'Failed to disable plugin';
                        } else {
                            $success = $pluginManager->enablePlugin($record->name);
                            $message = $success ? 'Plugin enabled successfully' : 'Failed to enable plugin';
                        }

                        Notification::make()
                            ->title($message)
                            ->success($success)
                            ->danger(! $success)
                            ->send();

                        if ($success) {
                            $record->update(['enabled' => ! $record->enabled]);
                        }
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('settings')
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('gray')
                    ->modalHeading('Plugin Settings')
                    ->modalWidth('lg')
                    ->form(function (Model $record): array {
                        // Dynamic form based on plugin requirements
                        return [
                            Forms\Components\KeyValue::make('settings')
                                ->label('Configuration')
                                ->default($record->settings ?? [])
                                ->helperText('Plugin-specific configuration options'),
                        ];
                    })
                    ->action(function (Model $record, array $data): void {
                        $record->update(['settings' => $data['settings']]);

                        Notification::make()
                            ->title('Settings updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('enable')
                    ->label('Enable Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $pluginManager = app(PluginManager::class);
                        $success = 0;

                        foreach ($records as $record) {
                            if (! $record->enabled && $pluginManager->enablePlugin($record->name)) {
                                $record->update(['enabled' => true]);
                                $success++;
                            }
                        }

                        Notification::make()
                            ->title("{$success} plugins enabled")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('disable')
                    ->label('Disable Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $pluginManager = app(PluginManager::class);
                        $success = 0;

                        foreach ($records as $record) {
                            if ($record->enabled && $pluginManager->disablePlugin($record->name)) {
                                $record->update(['enabled' => false]);
                                $success++;
                            }
                        }

                        Notification::make()
                            ->title("{$success} plugins disabled")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No plugins installed')
            ->emptyStateDescription('Install plugins to extend FilaMan functionality')
            ->emptyStateIcon('heroicon-o-puzzle-piece');
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
            'index' => \FilaMan\Admin\Filament\Resources\PluginResource\Pages\ListPlugins::route('/'),
            'create' => \FilaMan\Admin\Filament\Resources\PluginResource\Pages\InstallPlugin::route('/install'),
            'edit' => \FilaMan\Admin\Filament\Resources\PluginResource\Pages\EditPlugin::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pluginManager = app(PluginManager::class);
        $installedCount = count($pluginManager->getInstalledPlugins());

        return $installedCount > 0 ? (string) $installedCount : null;
    }
}
