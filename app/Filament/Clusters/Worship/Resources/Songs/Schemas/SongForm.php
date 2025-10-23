<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SongForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('author'),
                TextInput::make('copyright'),
                TextInput::make('key'),
                TextInput::make('tempo'),
                TextInput::make('audio'),
                TextInput::make('video'),
                TextInput::make('music'),
                TextInput::make('musictype'),
                TextInput::make('bible'),
                Textarea::make('lyrics')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('firstline'),
                TextInput::make('verseorder'),
                TextInput::make('tune'),
            ]);
    }
}
