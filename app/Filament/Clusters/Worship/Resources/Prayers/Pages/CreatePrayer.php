<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\PrayerResource;

class CreatePrayer extends CreateRecord
{
    protected static string $resource = PrayerResource::class;
}
