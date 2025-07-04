<?php

namespace Filament\Actions;

use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;

class BulkActionGroup extends ActionGroup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-tables::table.actions.open_bulk_actions.label'));

        $this->icon(FilamentIcon::resolve('tables::actions.open-bulk-actions') ?? Heroicon::EllipsisVertical);

        $this->defaultColor('gray');

        $this->button();

        $this->dropdownPlacement('bottom-start');

        $this->labeledFrom('sm');
    }

    /**
     * @return array<mixed>
     */
    public function getExtraDropdownAttributes(): array
    {
        return [
            'x-cloak' => true,
            'x-show' => 'getSelectedRecordsCount()',
            ...parent::getExtraAttributes(),
        ];
    }
}
