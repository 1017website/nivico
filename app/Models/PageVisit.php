<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'url', 'device', 'browser', 'platform', 'referrer',
        'visitor_hash', 'session_id', 'is_bot',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
    ];

    public function scopeHumans($q)
    {
        return $q->where('is_bot', false);
    }
}
