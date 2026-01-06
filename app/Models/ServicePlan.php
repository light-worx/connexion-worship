<?php

namespace Modules\Worship\Models;

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
}
