<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnsureUserIsAuthorized extends \NextApps\SwaggerUi\Http\Middleware\EnsureUserIsAuthorized
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (
            app()->environment('local') ||
            Gate::allows('viewSwaggerUI', [backpack_user()])
        ) {
            return $next($request);
        }

        abort(403);
    }
}
