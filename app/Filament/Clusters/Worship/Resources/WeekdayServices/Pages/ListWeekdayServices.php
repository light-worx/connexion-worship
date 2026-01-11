<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\WeekdayServiceResource;

class ListWeekdayServices extends ListRecords
{
    protected static string $resource = WeekdayServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
