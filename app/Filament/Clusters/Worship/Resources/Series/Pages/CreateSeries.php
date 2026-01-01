<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Series\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\SeriesResource;

class CreateSeries extends CreateRecord
{
    protected static string $resource = SeriesResource::class;
}
