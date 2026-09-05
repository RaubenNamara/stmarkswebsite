<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Club;

class ClubImage extends Model
{
    protected $fillable = ['club_id', 'image_path', 'caption'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}