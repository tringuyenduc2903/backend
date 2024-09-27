<?php

namespace App\Http\Middleware;

use Alert;
use App\Enums\EmployeePermission;
use App\Models\Employee;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureUserIsAuthorized extends \Wotz\SwaggerUi\Http\Middleware\EnsureUserIsAuthorized
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (backpack_auth()->guest()) {
            return $this->respondToUnauthorizedRequest($request);
        } elseif (! $this->ensureUserIsAuthorized()) {
            backpack_auth()->logout();

            return $this->respondToUnauthorizedRequest($request);
        } else {
            return $next($request);
        }
    }

    private function respondToUnauthorizedRequest(Request $request): Response|RedirectResponse
    {
        Alert::error(trans('backpack::base.unauthorized'))->flash();

        return $request->ajax() || $request->wantsJson()
            ? response(trans('backpack::base.unauthorized'), 401)
            : redirect()->route('backpack.auth.login');
    }

    private function ensureUserIsAuthorized(): bool
    {
        /** @var Employee $employee */
        $employee = backpack_user();

        return $employee->hasPermissionTo(
            EmployeePermission::API_DOCS,
            config('backpack.base.guard')
        );
    }
}
