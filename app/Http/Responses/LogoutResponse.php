<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        revoke_token('mobile');

        return response()->noContent();
    }
}
