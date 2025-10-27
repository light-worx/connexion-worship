<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PrayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('copyright'),
                Textarea::make('words')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
