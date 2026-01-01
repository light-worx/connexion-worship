<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\ChordResource;

class ListChords extends ListRecords
{
    protected static string $resource = ChordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
