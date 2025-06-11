<?php

namespace FilaMan\AdminPanelPlugin\Filament\Resources\PluginResource\Pages;

use FilaMan\AdminPanelPlugin\Filament\Resources\PluginResource;
use FilaMan\AdminPanelPlugin\Services\PluginManager;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPlugins extends ListRecords
{
    protected static string $resource = PluginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('discover')
                ->label('Discover Plugins')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray')
                ->action(function () {
                    $this->discoverAndSyncPlugins();
                }),

            Actions\Action::make('install')
                ->label('Install Plugin')
                ->icon('heroicon-o-plus-circle')
                ->url(PluginResource::getUrl('create')),
        ];
    }

    protected function discoverAndSyncPlugins(): void
    {
        $pluginManager = app(PluginManager::class);
        $availablePlugins = $pluginManager->getAvailablePlugins();
        $synced = 0;

        foreach ($availablePlugins as $pluginName => $pluginData) {
            if ($pluginManager->isPluginInstalled($pluginName)) {
                // Update existing plugin record
                $plugin = \FilaMan\AdminPanelPlugin\Models\Plugin::firstOrCreate(
                    ['name' => $pluginName],
                    [
                        'display_name' => $pluginData['name'] ?? $pluginName,
                        'description' => $pluginData['description'] ?? '',
                        'version' => $pluginData['version'] ?? '1.0.0',
                        'author' => $pluginData['authors'][0]['name'] ?? 'Unknown',
                        'enabled' => $pluginManager->isPluginEnabled($pluginName),
                    ]
                );

                if ($plugin->wasRecentlyCreated) {
                    $synced++;
                }
            }
        }

        Notification::make()
            ->title('Plugin discovery complete')
            ->body("{$synced} new plugins discovered and synced")
            ->success()
            ->send();
    }

    public function mount(): void
    {
        parent::mount();

        // Auto-discover on first load if no plugins exist
        if (\FilaMan\AdminPanelPlugin\Models\Plugin::count() === 0) {
            $this->discoverAndSyncPlugins();
        }
    }
}
