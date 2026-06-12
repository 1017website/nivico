<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name', 'account_number', 'account_holder', 'logo', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
