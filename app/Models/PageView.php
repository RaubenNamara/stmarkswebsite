<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = [
        'page_url',
        'page_type',
        'page_id',
        'ip_address',
        'user_agent',
        'view_date',
    ];

    protected $casts = [
        'view_date' => 'date',
    ];
}
