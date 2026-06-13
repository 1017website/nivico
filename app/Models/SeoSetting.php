<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'page_key', 'title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image', 'canonical_url', 'noindex',
    ];

    protected $casts = ['noindex' => 'boolean'];

    public static function for(string $pageKey): ?self
    {
        return static::where('page_key', $pageKey)->first();
    }
}
