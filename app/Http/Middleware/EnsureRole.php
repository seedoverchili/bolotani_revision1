<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            $login = $role === 'admin' && Route::has('admin.login') ? 'admin.login' : 'login';

            return redirect()->route($login);
        }

        abort_unless($user->role === $role, 403);

        return $next($request);
    }
}