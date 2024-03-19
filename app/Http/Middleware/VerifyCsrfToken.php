<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/web-api/auth/session/v2/verifySession',
        '/web-api/auth/session/v2/verifyOperatorPlayerSession',
        '/web-api/game-proxy/v2/GameName/Get',
        '/web-api/game-proxy/v2/Resources/GetByResourcesTypeIds',
        '/web-api/game-proxy/v2/GameRule/Get',
    ];

    protected function inExceptArray($request)
    {
        if (parent::inExceptArray($request)) {
            return true;
        }
        //adicionar logica para verificar as rotas dinamicas
        $path = $request->path();
        if (preg_match('#^game-api/.+/v2/(gameinfo/get|spin)$#i', $path)) {
            return true;
        }
    }
}
