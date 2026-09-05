<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class PageView extends Model
{
    protected string $table = 'page_views';
    protected array $fillable = ['page_url', 'page_type', 'page_id', 'ip_address', 'user_agent', 'view_date'];
    protected bool $timestamps = true;
}
