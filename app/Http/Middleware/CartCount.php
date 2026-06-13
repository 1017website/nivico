<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Share jumlah item keranjang ke semua view (badge header & bottom nav).
 */
class CartCount
{
    public function __construct(protected CartService $cart) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Hindari membuat baris cart untuk konteks yang tidak butuh:
        // panel admin, webhook server-to-server, dan request non-HTML (AJAX/JSON).
        $skip = $request->is('admin', 'admin/*', 'midtrans/*')
            || $request->expectsJson()
            || $request->ajax();

        if (! $skip) {
            View::share('cartCount', $this->cart->current()->totalQty());
        }

        return $next($request);
    }
}
