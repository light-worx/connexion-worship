<?php

namespace Modules\Worship\Providers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\People\Models\Group;

class SettingsProvider
{
    public function getSettingsTab(): Tab
    {
        return Tab::make('Worship')
            ->icon('heroicon-o-musical-note')
            ->schema([
                KeyValue::make('worship.order_of_service')->columnSpanFull(),
                TagsInput::make('worship.set_items'),
                Select::make('worship.online_service_group')->label('Online service group')
                    ->options(Group::all()->sortBy('groupname')->pluck('groupname', 'id'))
                    ->searchable(),
                TagsInput::make('worship.custom_service_times'),
            ]);
    }
}
