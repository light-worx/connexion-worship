<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\PrayerResource;

class EditPrayer extends EditRecord
{
    protected static string $resource = PrayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
