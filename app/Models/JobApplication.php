<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'full_name',
        'contact',
        'email',
        'address',
        'position',
        'file_path',
    ];
}