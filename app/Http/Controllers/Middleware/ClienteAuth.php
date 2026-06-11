<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClienteAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login');
        }
        return $next($request);
    }
}
