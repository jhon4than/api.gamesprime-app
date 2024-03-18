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

Route::post(
    '/web-api/auth/session/v2/verifySession',
    [GameController::class, 'verifySession']
);


Route::post(
    '/web-api/auth/session/v2/verifyOperatorPlayerSession',
    [GameController::class, 'verifyOperatorPlayerSession']
);
