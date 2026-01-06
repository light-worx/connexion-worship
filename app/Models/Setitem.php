<?php

namespace Modules\Worship\Models;

use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Setitem extends Model
{
    public $table = 'setitems';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
