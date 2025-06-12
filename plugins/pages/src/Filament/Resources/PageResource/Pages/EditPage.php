<?php

namespace FilaMan\Pages\Filament\Resources\PageResource\Pages;

use FilaMan\Pages\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('visit')
                ->label('View Page')
                ->url(fn (): string => "/pages/{$this->record->slug}")
                ->openUrlInNewTab()
                ->icon('heroicon-o-eye')
                ->color('gray'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
