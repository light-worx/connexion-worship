<?php

namespace Modules\Worship\Models;

use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ServicePlan extends Model
{
    protected $table = 'service_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date'
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function setitems()
    {
        return $this->hasMany(Setitem::class)->orderBy('sort_order');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function songSetitems()
    {
        return $this->setitems()->where('content_type', 'song');
    }

    public function prayerSetitems()
    {
        return $this->setitems()->where('content_type', 'prayer');
    }
    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeOnDate($query, Carbon|string $date)
    {
        return $query->whereDate('date', Carbon::parse($date));
    }

    /*
     |--------------------------------------------------------------------------
     | Convenience helpers
     |--------------------------------------------------------------------------
     */

    public function getKeyDate(): string
    {
        return $this->date->toDateString();
    }

    public function hasSeries(): bool
    {
        return $this->series_id !== null;
    }

    public function isFuture(): bool
    {
        return $this->date->isFuture();
    }

    public function isSunday(): bool
    {
        return $this->date->isSunday();
    }

    public function copyPlannedItemsToService(Service $service): void
    {
        // Find the current last position in the service
        $startOrder = (int) ($service->setitems()->max('sort_order') ?? 0);

        $this->setitems()
            ->orderBy('sort_order')
            ->get()
            ->each(function (Setitem $item, int $index) use ($service, $startOrder) {
                Setitem::create([
                    'service_id'   => $service->id,
                    'sort_order'   => $startOrder + $index + 1,

                    // hybrid content
                    'content_type' => $item->content_type,
                    'content_id'   => $item->content_id,

                    // fallback text items (if any later)
                    'title'        => $item->title,
                    'subtitle'     => $item->subtitle,
                    'extra'        => $item->extra,
                ]);
            });
    }

}
