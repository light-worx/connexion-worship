<?php

namespace Modules\Worship\Models;

use Illuminate\Database\Eloquent\Model;

class WeekdayService extends Model
{
    protected $table = 'weekday_services';

    protected $guarded = ['id'];
}
