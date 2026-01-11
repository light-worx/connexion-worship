<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\WeekdayServiceResource;

class EditWeekdayService extends EditRecord
{
    protected static string $resource = WeekdayServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
