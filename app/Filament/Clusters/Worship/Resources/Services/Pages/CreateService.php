<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use Modules\Worship\Models\Setitem;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        $id = $this->record->id;
        $settings=setting('order_of_service');
        $items=explode(',',$settings[$this->record->servicetime]);
        foreach ($items as $ndx=>$item){
            if ($item=="Bible reading"){
                $subtitle = $this->record->reading;
            } elseif ($item=="Sermon"){
                $subtitle = "Michael Bishop";
            } else {
                $subtitle = null;
            }
            Setitem::create([
                'service_id' => $id,
                'title' => $item,
                'subtitle' => $subtitle,
                'sort_order' => $ndx
            ]);
        }
    }
}
