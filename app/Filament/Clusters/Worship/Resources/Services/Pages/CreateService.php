<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use Modules\Worship\Models\ServicePlan;
use Modules\Worship\Models\Setitem;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        $service = $this->record;
        if ($service->setitems()->whereNotNull('content_type')->exists()) {
            return;
        }
        $settings = setting('order_of_service');
        $items = explode(',', $settings[$service->servicetime]);

        foreach ($items as $ndx => $item) {
            if ($item === 'Bible reading') {
                $subtitle = $service->reading;
            } elseif ($item === 'Sermon') {
                $subtitle = 'Michael Bishop';
            } else {
                $subtitle = null;
            }

            Setitem::create([
                'service_id' => $service->id,
                'title'      => $item,
                'subtitle'   => $subtitle,
                'sort_order' => $ndx,
            ]);
        }
        if (! ($this->data['include_planner_items'] ?? true)) {
            return;
        }
        $plan = ServicePlan::whereDate('date', $service->servicedate)->first();
        if ($plan) {
            $plan->copyPlannedItemsToService($service);
        }
    }
}
