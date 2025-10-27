<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\PrayerResource;

class ListPrayers extends ListRecords
{
    protected static string $resource = PrayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
