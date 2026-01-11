<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WeekdayServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'fixed' => 'Fixed',
                        'easter-relative' => 'Relative to Easter'
                    ]),
                TextInput::make('month')->numeric(),
                TextInput::make('day')->numeric(),
                TextInput::make('offset')->numeric()
            ]);
    }
}
