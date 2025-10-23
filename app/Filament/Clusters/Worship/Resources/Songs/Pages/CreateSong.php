<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\SongResource;

class CreateSong extends CreateRecord
{
    protected static string $resource = SongResource::class;
}
