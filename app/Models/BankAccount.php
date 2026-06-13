<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'bank_name', 'account_number', 'account_holder', 'logo', 'is_active', 'sort_order',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
