<?php

namespace FilaMan\Admin\Filament\Resources\PluginResource\Pages;

use FilaMan\Admin\Filament\Resources\PluginResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPlugin extends EditRecord
{
    protected static string $resource = PluginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uninstall')
                ->label('Uninstall')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Uninstall Plugin')
                ->modalDescription('Are you sure you want to uninstall this plugin? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, uninstall')
                ->visible(fn () => ! $this->record->isCorePlugin())
                ->action(function () {
                    $pluginManager = app(\FilaMan\Admin\Services\PluginManager::class);

                    if ($pluginManager->uninstallPlugin($this->record->name)) {
                        $this->record->delete();

                        Notification::make()
                            ->title('Plugin uninstalled')
                            ->body('The plugin has been successfully uninstalled')
                            ->success()
                            ->send();

                        return redirect($this->getResource()::getUrl('index'));
                    } else {
                        Notification::make()
                            ->title('Uninstall failed')
                            ->body('Failed to uninstall the plugin')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle enable/disable through the plugin manager
        if (isset($data['enabled']) && $data['enabled'] !== $this->record->enabled) {
            $pluginManager = app(\FilaMan\Admin\Services\PluginManager::class);

            if ($data['enabled']) {
                $pluginManager->enablePlugin($this->record->name);
            } else {
                $pluginManager->disablePlugin($this->record->name);
            }
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Plugin updated';
    }
}
