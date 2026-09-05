<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Contact extends Model
{
    protected string $table = 'contacts';
    protected array $fillable = ['name', 'telephone', 'email', 'message', 'is_read'];
    protected array $searchable = ['name', 'email', 'message'];
}
