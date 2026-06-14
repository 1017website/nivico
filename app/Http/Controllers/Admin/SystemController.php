<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Whitelist perintah yang boleh dijalankan dari panel.
     * key => [label, deskripsi, artisan command, arguments]
     */
    protected function commands(): array
    {
        return [
            'migrate' => [
                'label' => 'Jalankan Migrasi',
                'desc'  => 'php artisan migrate --force — terapkan migration database yang belum dijalankan.',
                'cmd'   => 'migrate',
                'args'  => ['--force' => true],
                'danger'=> false,
            ],
            'optimize_clear' => [
                'label' => 'Bersihkan Cache',
                'desc'  => 'php artisan optimize:clear — hapus cache config, route, view, event & compiled.',
                'cmd'   => 'optimize:clear',
                'args'  => [],
                'danger'=> false,
            ],
            'storage_link' => [
                'label' => 'Buat Symlink Storage',
                'desc'  => 'php artisan storage:link — hubungkan public/storage ke storage/app/public.',
                'cmd'   => 'storage:link',
                'args'  => [],
                'danger'=> false,
            ],
            'config_cache' => [
                'label' => 'Cache Konfigurasi',
                'desc'  => 'php artisan config:cache — gabung & cache semua config (produksi).',
                'cmd'   => 'config:cache',
                'args'  => [],
                'danger'=> false,
            ],
            'route_cache' => [
                'label' => 'Cache Route',
                'desc'  => 'php artisan route:cache — cache definisi route (produksi).',
                'cmd'   => 'route:cache',
                'args'  => [],
                'danger'=> false,
            ],
            'view_cache' => [
                'label' => 'Cache View',
                'desc'  => 'php artisan view:cache — precompile seluruh Blade view.',
                'cmd'   => 'view:cache',
                'args'  => [],
                'danger'=> false,
            ],
            'queue_restart' => [
                'label' => 'Restart Queue Worker',
                'desc'  => 'php artisan queue:restart — beri sinyal worker untuk restart setelah job berjalan.',
                'cmd'   => 'queue:restart',
                'args'  => [],
                'danger'=> false,
            ],
        ];
    }

    public function index()
    {
        return view('admin.system.index', [
            'commands' => $this->commands(),
        ])->with('seoKey', null);
    }

    public function run(Request $request)
    {
        // Lapis keamanan kedua: hanya Super Admin (selain middleware).
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Khusus Super Admin.');

        $data = $request->validate([
            'command' => 'required|string',
        ]);

        $commands = $this->commands();
        $key = $data['command'];

        if (! isset($commands[$key])) {
            return back()->with('error', 'Perintah tidak dikenali.');
        }

        $def = $commands[$key];

        try {
            $exit = Artisan::call($def['cmd'], $def['args']);
            $output = trim(Artisan::output());

            // catat ke activity log manual
            \App\Models\ActivityLog::create([
                'user_id'      => auth()->id(),
                'user_name'    => auth()->user()->name,
                'action'       => 'system',
                'subject_type' => 'System',
                'subject_id'   => 0,
                'description'  => 'Menjalankan: php artisan '.$def['cmd'],
                'properties'   => ['exit' => $exit, 'output' => mb_substr($output, 0, 2000)],
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 255),
            ]);

            return back()->with('cmd_result', [
                'label'  => $def['label'],
                'exit'   => $exit,
                'output' => $output !== '' ? $output : '(tidak ada output)',
                'ok'     => $exit === 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('System command gagal', ['cmd' => $def['cmd'], 'msg' => $e->getMessage()]);
            return back()->with('cmd_result', [
                'label'  => $def['label'],
                'exit'   => 1,
                'output' => 'ERROR: '.$e->getMessage(),
                'ok'     => false,
            ]);
        }
    }
}
