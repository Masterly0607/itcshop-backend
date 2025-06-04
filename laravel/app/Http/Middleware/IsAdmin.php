<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin middleware: must register this middleware in bootstrap/app.php
        if (Auth::user() && Auth::user()->is_admin = 1) {
            return $next($request); // passes the request to the next step (like controller) if the is_admin = 1
        }
        return response(
            [
                'message' => "You don't have permission to perform this action"
            ],
            403
        );
    }
}
