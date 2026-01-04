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

    public function setitemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function elementType()
    {
        return $this->belongsTo(ServiceElementType::class, 'element_type_id');
    }

    public function song() { 
        return $this->belongsTo(Song::class, 'content_id'); 
    }
    
    public function prayer() { 
        return $this->belongsTo(Prayer::class, 'content_id'); 
    }
    
    public function reading() { 
        return null; //$this->belongsTo(BibleReading::class, 'content_id'); 
    }

    public function preacher() { 
        return $this->belongsTo(Person::class, 'content_id'); 
    }

}
