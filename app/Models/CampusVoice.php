<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampusVoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_name',
        'title',
        'slug',
        'summary',
        'author_bio',
        'content',
        'featured_image',
        'category',
        'author',
        'featured',
        'status',
        'views',
        'published_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($campusVoice) {
            if (!$campusVoice->slug) {
                $campusVoice->slug = Str::slug($campusVoice->title);
            }
        });

        static::updating(function ($campusVoice) {
            if ($campusVoice->isDirty('title') && !$campusVoice->slug) {
                $campusVoice->slug = Str::slug($campusVoice->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByStudentName($query)
    {
        return $query->orderBy('student_name', 'asc');
    }

    public function scopeSearchByStudentName($query, $search)
    {
        if ($search) {
            return $query->where('student_name', 'like', '%' . $search . '%');
        }
        return $query;
    }

    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200);
        return max(1, $minutes) . ' min read';
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
}
