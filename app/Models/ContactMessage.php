<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'topic', 'message', 'is_read',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = ['is_read' => 'boolean'];
}
