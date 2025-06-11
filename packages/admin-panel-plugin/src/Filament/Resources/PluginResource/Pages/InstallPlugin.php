<?php

namespace FilaMan\AdminPanelPlugin\Filament\Resources\PluginResource\Pages;

use FilaMan\AdminPanelPlugin\Filament\Resources\PluginResource;
use FilaMan\AdminPanelPlugin\Services\PluginManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class InstallPlugin extends CreateRecord
{
    protected static string $resource = PluginResource::class;

    protected static ?string $title = 'Install New Plugin';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Available Plugins')
                    ->description('Select a plugin to install from the available plugins')
                    ->schema([
                        Forms\Components\Select::make('plugin_name')
                            ->label('Plugin')
                            ->options(function () {
                                $pluginManager = app(PluginManager::class);
                                $availablePlugins = $pluginManager->getAvailablePlugins();
                                $options = [];

                                foreach ($availablePlugins as $pluginName => $pluginData) {
                                    if (! $pluginManager->isPluginInstalled($pluginName)) {
                                        $options[$pluginName] = $pluginData['name'].' - '.$pluginData['description'];
                                    }
                                }

                                return $options;
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Select a plugin from the discovered plugins'),
                    ]),

                Forms\Components\Section::make('Installation Options')
                    ->schema([
                        Forms\Components\Toggle::make('enable_after_install')
                            ->label('Enable after installation')
                            ->default(true)
                            ->helperText('Automatically enable the plugin after installation'),

                        Forms\Components\Toggle::make('run_migrations')
                            ->label('Run migrations')
                            ->default(true)
                            ->helperText('Run plugin database migrations during installation'),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $pluginManager = app(PluginManager::class);
        $pluginName = $data['plugin_name'];
        $availablePlugins = $pluginManager->getAvailablePlugins();

        if (! isset($availablePlugins[$pluginName])) {
            throw new \Exception("Plugin {$pluginName} not found");
        }

        $pluginData = $availablePlugins[$pluginName];

        // Install the plugin
        $success = $pluginManager->installPlugin($pluginName);

        if (! $success) {
            Notification::make()
                ->title('Installation failed')
                ->body("Failed to install {$pluginName}")
                ->danger()
                ->send();

            throw new \Exception('Plugin installation failed');
        }

        // Create the plugin record
        $plugin = \FilaMan\AdminPanelPlugin\Models\Plugin::create([
            'name' => $pluginName,
            'display_name' => $pluginData['name'] ?? $pluginName,
            'description' => $pluginData['description'] ?? '',
            'version' => $pluginData['version'] ?? '1.0.0',
            'author' => $pluginData['authors'][0]['name'] ?? 'Unknown',
            'enabled' => $data['enable_after_install'] ?? true,
        ]);

        Notification::make()
            ->title('Plugin installed successfully')
            ->body("{$plugin->display_name} has been installed")
            ->success()
            ->send();

        return $plugin;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
