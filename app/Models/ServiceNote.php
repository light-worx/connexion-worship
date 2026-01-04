<?php

namespace Modules\Worship\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceNote extends Model
{
    protected $guarded = ['id'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function renderForPdf(): string
    {
        return $this->body ?? '';
    }
}
