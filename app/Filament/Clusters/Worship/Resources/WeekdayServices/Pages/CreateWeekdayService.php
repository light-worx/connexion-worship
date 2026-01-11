<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\WeekdayServiceResource;

class CreateWeekdayService extends CreateRecord
{
    protected static string $resource = WeekdayServiceResource::class;
}
