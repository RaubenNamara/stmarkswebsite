<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChristmasCantata extends Model
{
    protected $fillable = [
        'title',
        'choir',
        'date',
        'description',
        'image',
        'video'
    ];

    // Cast date to Carbon so formatting / HTML date input works
    protected $casts = [
        'date' => 'date',
    ];
}