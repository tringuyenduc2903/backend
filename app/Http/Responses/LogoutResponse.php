<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse extends \Laravel\Fortify\Http\Responses\LogoutResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        revoke_token('mobile');

        return response()->json('', 204);
    }
}
