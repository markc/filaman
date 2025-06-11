<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LocalAutoLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only auto-login in local environment (not testing)
        if (app()->environment('local') && ! app()->environment('testing') && ! Auth::check()) {
            // Find first admin user or create one if none exists
            $adminUser = User::where('role', 'admin')->first();

            if (! $adminUser) {
                $adminUser = User::factory()->create([
                    'name' => 'Local Admin',
                    'email' => 'admin@local.dev',
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            }

            // Auto-login the admin user
            Auth::login($adminUser);
        }

        return $next($request);
    }
}
