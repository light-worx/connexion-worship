<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Series\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Worship\Models\Series;

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('series')
                    ->searchable(),
                TextColumn::make('startingdate')
                    ->date('Y-m-d')
                    ->label('Starting date')
                    ->sortable(),
                ImageColumn::make('image')
                    ->state(function (Series $record) {
                        return url('/storage/' . $record->image);
                }),
            ])
            ->defaultSort('startingdate', 'desc')
            ->filters([
                //
            ])
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
