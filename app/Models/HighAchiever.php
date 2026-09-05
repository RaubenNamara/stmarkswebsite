<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HighAchiever extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'year',
        'exam',
        'division',
        'description',
    ];
}