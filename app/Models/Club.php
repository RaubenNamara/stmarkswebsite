<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClubImage;

class Club extends Model
{
    protected $fillable = ['title', 'slug', 'content'];

    public function images()
    {
        return $this->hasMany(ClubImage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Use slug instead of ID in routes
    |--------------------------------------------------------------------------
    */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}