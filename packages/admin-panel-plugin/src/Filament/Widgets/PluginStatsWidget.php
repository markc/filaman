<?php

namespace FilaMan\AdminPanelPlugin\Filament\Widgets;

use FilaMan\AdminPanelPlugin\Models\Plugin;
use FilaMan\AdminPanelPlugin\Services\PluginManager;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PluginStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pluginManager = app(PluginManager::class);
        $availablePlugins = $pluginManager->getAvailablePlugins();
        $installedPlugins = $pluginManager->getInstalledPlugins();
        $enabledCount = Plugin::enabled()->count();

        return [
            Stat::make('Available Plugins', count($availablePlugins))
                ->description('Total plugins discovered')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray'),

            Stat::make('Installed Plugins', count($installedPlugins))
                ->description($enabledCount.' enabled')
                ->icon('heroicon-o-puzzle-piece')
                ->color('primary'),

            Stat::make('Plugin Updates', $this->getUpdatesCount())
                ->description('Updates available')
                ->icon('heroicon-o-arrow-path')
                ->color($this->getUpdatesCount() > 0 ? 'warning' : 'success'),
        ];
    }

    protected function getUpdatesCount(): int
    {
        // In a real implementation, this would check for actual updates
        // For now, return 0
        return 0;
    }
}
