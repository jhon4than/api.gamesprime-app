<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CopyController;
use App\Http\Controllers\GameController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// https://m.pg-nmga.com
// /126/
// index.html?btt=1&ot=AFECCA87-73E4-45B9-9C79-E64413F96211&ops=donaldbet_G6aAtn0q7iU35lrT%21%21b1&l=pt&f=https%3A%2F%2Fdonald.bet%2Fcasino%2Flive&__refer=m.pgr-nmga.com&or=static.pg-nmga.com&__hv=1fb275f1

// Route::get('/{path}', [CopyController::class, 'copy'])
//     ->where('path', '.*');

    // https://api.gamesprime-app.test/web-api/auth/session/v2/verifyOperatorPlayerSession?traceId=ZFHIVP08
//https://api.gamesprime.fun/web-api/game-proxy/v2/GameName/Get?traceId=NQCQQY18
//https://api.pg-nmga.com/game-api/fortune-tiger/v2/GameInfo/Get?traceId=ROHVDC18


Route::post(
    '/web-api/auth/session/v2/verifySession',
    [GameController::class, 'verifySession']
);


Route::post(
    '/web-api/auth/session/v2/verifyOperatorPlayerSession',
    [GameController::class, 'verifySession']
);

Route::post(
    '/web-api/game-proxy/v2/GameName/Get',
    [GameController::class, 'getGameName']
);

Route::post(
    '/game-api/{game}/v2/GameInfo/Get',
    [GameController::class, 'getGameInfo']
);
//https://api.gamesprime.fun/game-api/diaochan/v2/spin?traceId=SCXTUW20
Route::post(
    '/game-api/{game}/v2/Spin',
    [GameController::class, 'spin']
);

Route::post(
    '/game-api/{game}/v2/spin',
    [GameController::class, 'spin']
);
 
//https://api.pg-nmga.com/web-api/game-proxy/v2/Resources/GetByResourcesTypeIds?traceId=PZBMMQ18
Route::post(
    '/web-api/game-proxy/v2/Resources/GetByResourcesTypeIds',
    [GameController::class, 'GetByResourcesTypeIds']
);

Route::post(
    '/web-api/game-proxy/v2/GameRule/Get',
    [GameController::class, 'getGameRule']
);
