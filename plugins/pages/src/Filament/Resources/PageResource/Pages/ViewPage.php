<?php

namespace FilaMan\Pages\Filament\Resources\PageResource\Pages;

use FilaMan\Pages\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPage extends ViewRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('visit')
                ->url(fn (): string => "/pages/{$this->record->slug}")
                ->openUrlInNewTab()
                ->icon('heroicon-o-eye'),
        ];
    }
}
