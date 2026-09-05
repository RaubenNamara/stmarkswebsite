<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class JobApplication extends Model
{
    protected string $table = 'job_applications';
    protected array $fillable = ['full_name', 'contact', 'email', 'address', 'position', 'file_path'];
    protected array $searchable = ['full_name', 'position', 'email'];
}
