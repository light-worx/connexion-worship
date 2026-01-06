<?php

namespace Modules\Worship\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Song extends Model
{
    public $table = 'songs';
    protected $guarded = ['id'];

    public function setitem(): MorphMany
    {
        return $this->morphMany(Setitem::class,'setitemable');
    }

    public function getLastUsedAttribute(): ?string
    {
        $service = Service::query()
            ->whereDate('servicedate', '<', now())
            ->whereHas('setitems', function ($q) {
                $q->where('content_type', 'song')
                ->where('content_id', $this->id);
            })
            ->latest('servicedate')
            ->first();

        return $service
            ? Carbon::parse($service->servicedate)->diffForHumans()
            : null;
    }
}
