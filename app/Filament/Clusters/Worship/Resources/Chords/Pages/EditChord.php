<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\ChordResource;

class EditChord extends EditRecord
{
    protected static string $resource = ChordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
