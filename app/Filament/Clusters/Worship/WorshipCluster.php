<?php

namespace Modules\Worship\Filament\Clusters\Worship;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class WorshipCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::MusicalNote;
}
