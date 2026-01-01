<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Series\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\SeriesResource;

class ListSeries extends ListRecords
{
    protected static string $resource = SeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
