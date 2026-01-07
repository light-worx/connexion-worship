<?php

namespace Modules\Worship\Models;

use App\Models\Person;
use App\Traits\Taggable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use Taggable;

    public $table = 'services';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function setitems(): HasMany
    {
        return $this->hasMany(Setitem::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function copyPlanToService(Collection $plannedItems): void
    {
        $plannedItems->each(function (Setitem $item) {
            $this->setitems()->create([
                'content_id'   => $item->content_id,
                'content_type' => $item->content_type,
                'sort_order'   => $item->sort_order,
                'title'        => $item->title,
                'subtitle'     => $item->subtitle,
                'extra'        => $item->extra,
            ]);
        });
    }
}
