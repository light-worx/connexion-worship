<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Prayers;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages\CreatePrayer;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages\EditPrayer;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Pages\ListPrayers;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Schemas\PrayerForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\Prayers\Tables\PrayersTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;
use Modules\Worship\Models\Prayer;

class PrayerResource extends Resource
{
    protected static ?string $model = Prayer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PrayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrayersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrayers::route('/'),
            'create' => CreatePrayer::route('/create'),
            'edit' => EditPrayer::route('/{record}/edit'),
        ];
    }
}
