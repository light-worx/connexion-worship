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
                TextColumn::make('servicedate')
                    ->date()
                    ->sortable(),
                TextColumn::make('servicetime')
                    ->searchable(),
                TextColumn::make('sermon_title')
                    ->searchable(),
                TextColumn::make('audio')
                    ->searchable(),
                TextColumn::make('video')
                    ->searchable(),
                TextColumn::make('reading')
                    ->searchable(),
                TextColumn::make('series.id')
                    ->searchable(),
                TextColumn::make('person_id')
                    ->numeric()
                    ->sortable(),
            ])
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
