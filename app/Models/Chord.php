<?php

namespace Modules\Worship\Models;

use Illuminate\Database\Eloquent\Model;

class Chord extends Model
{
    public $table = 'chords';
    protected $guarded = ['id'];
    public $timestamps = false;
}
