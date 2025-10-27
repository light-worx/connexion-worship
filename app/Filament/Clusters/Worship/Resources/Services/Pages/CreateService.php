<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\ServiceResource;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
}
