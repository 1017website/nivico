<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['product_id', 'path', 'sort_order',
        'created_by', 'updated_by', 'deleted_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
