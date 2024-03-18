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
        '/game-api/fortune-tiger/v2/GameInfo/Get',
        '/game-api/fortune-tiger/v2/Spin',
        '/web-api/game-proxy/v2/Resources/GetByResourcesTypeIds'
    ];
}
