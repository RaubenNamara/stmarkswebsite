<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoCurricular extends Model
{
    protected $table = 'co_curriculars';

    protected $fillable = [
        'title',
        'content',
        'image',
        'video'
    ];
}