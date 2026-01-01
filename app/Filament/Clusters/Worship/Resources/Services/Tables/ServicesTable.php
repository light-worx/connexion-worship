<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('servicedate')->label('Date of service')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('servicetime')->label('Time')
                    ->searchable(),
                TextColumn::make('reading')
                    ->searchable(),
                TextColumn::make('series.series')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort(fn ($query) => $query->orderBy('servicedate', 'desc')->orderBy('servicetime', 'asc'))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
