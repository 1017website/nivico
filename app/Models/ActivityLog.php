<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address', 'user_agent',
    ];

    protected $casts = ['properties' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Tambah',
            'updated' => 'Ubah',
            'deleted' => 'Hapus',
            'restored'=> 'Pulihkan',
            'login'   => 'Masuk',
            'logout'  => 'Keluar',
            default   => ucfirst($this->action),
        };
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            'created' => 'b-completed',
            'updated' => 'b-paid',
            'deleted' => 'b-cancelled',
            'login'   => 'b-processing',
            'logout'  => 'b-shipped',
            default   => 'b-pending',
        };
    }
}
