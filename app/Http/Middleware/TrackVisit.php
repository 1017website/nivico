<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat kunjungan halaman toko ke tabel page_visits.
 * - Skip: admin, webhook, AJAX/JSON, request non-GET, dan asset.
 * - Privasi: IP tidak disimpan mentah, hanya hash (IP+UA) untuk hitung unik.
 * - Deteksi device/browser/platform dari User-Agent secara native (tanpa library).
 */
class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // hanya catat GET halaman HTML sisi toko
        $skip = $request->isMethod('GET') === false
            || $request->is('admin', 'admin/*', 'midtrans/*', 'login', 'register', 'logout')
            || $request->expectsJson()
            || $request->ajax();

        if ($skip) {
            return $response;
        }

        // jangan catat respon redirect / error
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return $response;
        }

        try {
            $ua = (string) $request->userAgent();
            $info = $this->parseUserAgent($ua);

            PageVisit::create([
                'url'          => substr($request->path(), 0, 512),
                'device'       => $info['device'],
                'browser'      => $info['browser'],
                'platform'     => $info['platform'],
                'referrer'     => $this->refererHost($request),
                'visitor_hash' => hash('sha256', $request->ip().'|'.$ua),
                'session_id'   => substr(Session::getId(), 0, 64),
                'is_bot'       => $info['is_bot'],
            ]);
        } catch (\Throwable $e) {
            // jangan ganggu request bila tracking gagal
        }

        return $response;
    }

    /** Ambil host dari referer (sumber traffic), tanpa query/path. */
    protected function refererHost(Request $request): ?string
    {
        $ref = $request->headers->get('referer');
        if (! $ref) {
            return null;
        }
        $host = parse_url($ref, PHP_URL_HOST);
        if (! $host) {
            return null;
        }
        // anggap kunjungan dari domain sendiri sebagai "Direct/Internal"
        if ($host === $request->getHost()) {
            return null;
        }
        return substr($host, 0, 255);
    }

    /** Deteksi device/browser/platform/bot dari User-Agent. */
    protected function parseUserAgent(string $ua): array
    {
        $u = strtolower($ua);

        $isBot = $ua === '' || preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|whatsapp|telegrambot|google|yandex|baidu|duckduck|semrush|ahrefs|mj12|dotbot/i', $ua);

        // device
        $device = 'desktop';
        if (preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $ua)) {
            $device = 'tablet';
        } elseif (preg_match('/mobi|iphone|ipod|android.*mobile|blackberry|opera mini|iemobile/i', $ua)) {
            $device = 'mobile';
        }

        // platform
        $platform = 'Lainnya';
        if (str_contains($u, 'windows'))      $platform = 'Windows';
        elseif (str_contains($u, 'android'))  $platform = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/', $u)) $platform = 'iOS';
        elseif (str_contains($u, 'mac os'))   $platform = 'macOS';
        elseif (str_contains($u, 'linux'))    $platform = 'Linux';

        // browser (urutan penting: cek yang lebih spesifik dulu)
        $browser = 'Lainnya';
        if (str_contains($u, 'edg/'))                       $browser = 'Edge';
        elseif (str_contains($u, 'opr/') || str_contains($u, 'opera')) $browser = 'Opera';
        elseif (str_contains($u, 'samsungbrowser'))         $browser = 'Samsung Internet';
        elseif (str_contains($u, 'chrome') && ! str_contains($u, 'edg/')) $browser = 'Chrome';
        elseif (str_contains($u, 'firefox'))                $browser = 'Firefox';
        elseif (str_contains($u, 'safari') && ! str_contains($u, 'chrome')) $browser = 'Safari';

        return [
            'device'   => $device,
            'browser'  => $browser,
            'platform' => $platform,
            'is_bot'   => (bool) $isBot,
        ];
    }
}
