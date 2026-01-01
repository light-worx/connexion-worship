<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\ChordResource;

class CreateChord extends CreateRecord
{
    protected static string $resource = ChordResource::class;
}
