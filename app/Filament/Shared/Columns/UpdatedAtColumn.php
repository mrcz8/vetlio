<?php

namespace App\Filament\Shared\Columns;

use Filament\Tables\Columns\TextColumn;

class UpdatedAtColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('updated_at');
        $this->label(__('tables.columns.updated_at'));
        $this->dateTime();
        $this->sortable();
        $this->toggleable(isToggledHiddenByDefault: true);
    }
}
