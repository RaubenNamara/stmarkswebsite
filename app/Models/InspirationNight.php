<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspirationNight extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'video',
        'date',
        'speaker'
    ];
}