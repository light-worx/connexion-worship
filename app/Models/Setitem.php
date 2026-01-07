<?php

namespace Modules\Worship\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setitem extends Model
{
    public $table = 'setitems';
    protected $guarded = ['id'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function content()
    {
        return match ($this->content_type) {
            'song'    => Song::find($this->content_id),
            'prayer'  => Prayer::find($this->content_id),
            default   => null,
        };
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'content_id');
    }

    public function prayer()
    {
        return $this->belongsTo(Prayer::class, 'content_id');
    }

}
