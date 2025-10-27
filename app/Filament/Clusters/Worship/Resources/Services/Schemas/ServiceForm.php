<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('servicedate')
                    ->required(),
                TextInput::make('servicetime')
                    ->required(),
                TextInput::make('sermon_title')
                    ->required(),
                TextInput::make('audio')
                    ->required(),
                TextInput::make('video')
                    ->required(),
                TextInput::make('reading')
                    ->required(),
                Select::make('series_id')
                    ->relationship('series', 'id')
                    ->required(),
                TextInput::make('person_id')
                    ->required()
                    ->numeric(),
                TextInput::make('tags')
                    ->required(),
            ]);
    }
}
