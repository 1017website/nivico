<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Category $c) {
            if (empty($c->slug)) {
                $c->slug = Str::slug($c->name);
            }
        });
    }


    public function iconHtml(?string $fallback = 'fa-solid fa-tag'): HtmlString
    {
        $icon = trim((string) ($this->icon ?: $fallback));

        if ($icon === '') {
            $icon = $fallback;
        }

        // Backward compatible untuk data lama yang masih berupa SVG mentah.
        if (str_starts_with($icon, '<svg')) {
            return new HtmlString($icon);
        }

        // Format baru: simpan class Font Awesome, render sebagai <i>.
        if (str_contains($icon, 'fa-')) {
            return new HtmlString('<i class="'.e($icon).'" aria-hidden="true"></i>');
        }

        // Backward compatible untuk data lama berupa emoji.
        return new HtmlString(e($icon));
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
