<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait audit: otomatis mengisi created_by / updated_by / deleted_by
 * dan mencatat aktivitas ke tabel activity_logs.
 *
 * Pakai bersama Illuminate\Database\Eloquent\SoftDeletes pada model.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = $model->created_by ?? Auth::id();
                $model->updated_by = $model->updated_by ?? Auth::id();
            }
        });

        static::created(function ($model) {
            $model->logActivity('created', 'Membuat '.$model->auditLabel());
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::updated(function ($model) {
            // jangan log bila hanya perubahan timestamp/audit
            $changed = collect($model->getChanges())
                ->keys()
                ->reject(fn ($k) => in_array($k, ['updated_at', 'updated_by', 'created_by', 'deleted_by', 'remember_token']))
                ->all();

            if (! empty($changed)) {
                $model->logActivity('updated', 'Memperbarui '.$model->auditLabel(), ['fields' => $changed]);
            }
        });

        static::deleting(function ($model) {
            // set deleted_by saat soft delete (bukan force delete)
            if (Auth::check() && method_exists($model, 'trashed') && ! $model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', 'Menghapus '.$model->auditLabel());
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    /** Label manusiawi untuk log. Override di model bila perlu. */
    public function auditLabel(): string
    {
        $name = class_basename($this);
        $ident = $this->name ?? $this->title ?? $this->order_number ?? $this->code ?? $this->getKey();
        return $name.' #'.$ident;
    }

    public function logActivity(string $action, ?string $description = null, array $properties = []): void
    {
        // hindari logging untuk model ActivityLog sendiri
        if ($this instanceof ActivityLog) {
            return;
        }

        // jangan log bila tidak ada user login (mis. seeder, command, webhook)
        if (! Auth::check()) {
            return;
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'user_name'    => optional(Auth::user())->name,
            'action'       => $action,
            'subject_type' => static::class,
            'subject_id'   => $this->getKey(),
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
