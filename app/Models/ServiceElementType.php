<?php

namespace Modules\Worship\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceElementType extends Model
{
    public $table = 'service_element_types';
    protected $guarded = ['id'];
    public $timestamps = false;

}
