<?php

namespace Modules\Worship\Models;

use App\Models\Person;
use App\Traits\Taggable;
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
}
