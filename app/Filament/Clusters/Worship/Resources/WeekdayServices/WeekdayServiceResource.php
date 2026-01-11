<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\CreateWeekdayService;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\EditWeekdayService;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\ListWeekdayServices;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Schemas\WeekdayServiceForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\WeekdayServices\Tables\WeekdayServicesTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;
use Modules\Worship\Models\WeekdayService;

class WeekdayServiceResource extends Resource
{
    protected static ?string $model = WeekdayService::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static ?string $cluster = WorshipCluster::class;

    public static function form(Schema $schema): Schema
    {
        return WeekdayServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeekdayServicesTable::configure($table);
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
            'index' => ListWeekdayServices::route('/'),
            'create' => CreateWeekdayService::route('/create'),
            'edit' => EditWeekdayService::route('/{record}/edit'),
        ];
    }
}
