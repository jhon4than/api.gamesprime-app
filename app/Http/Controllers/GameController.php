<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function verifySession()
    {
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->rJSON(), true);

        // Return the data as a JSON response
        return response()->json($data);
    }

    public function verifyOperatorPlayerSession()
    {
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->rJSON(), true);

        // Return the data as a JSON response
        return response()->json($data);
    }

    public function rJSON()
    {
        return '{
            "dt": {
                "oj": {
                    "jid": 0
                },
                "pid": "2z3oHNpTBq",
                "pcd": "admin@demo.com",
                "tk": "CC05AFC4-9059-49D3-B4DE-3DF2CDAADC47",
                "st": 1,
                "geu": "game-api/fortune-tiger/",
                "lau": "/game-api/lobby/",
                "bau": "web-api/game-proxy/",
                "cc": "BRL",
                "cs": "R$",
                "nkn": "admin@demo.com",
                "gm": [
                    {
                        "gid": 126,
                        "msdt": 1638432092000,
                        "medt": 1638432092000,
                        "st": 1,
                        "amsg": "",
                        "rtp": {
                            "df": {
                                "min": 96.81,
                                "max": 96.81
                            }
                        },
                        "mxe": 2500,
                        "mxehr": 8960913
                    }
                ],
                "uiogc": {
                    "bb": 0,
                    "grtp": 1,
                    "gec": 0,
                    "cbu": 0,
                    "cl": 0,
                    "bf": 0,
                    "mr": 0,
                    "phtr": 0,
                    "vc": 0,
                    "bfbsi": 1,
                    "bfbli": 1,
                    "il": 0,
                    "rp": 0,
                    "gc": 1,
                    "ign": 1,
                    "tsn": 0,
                    "we": 0,
                    "gsc": 0,
                    "bu": 0,
                    "pwr": 0,
                    "hd": 0,
                    "et": 0,
                    "np": 0,
                    "igv": 0,
                    "as": 0,
                    "asc": 0,
                    "std": 0,
                    "hnp": 0,
                    "ts": 0,
                    "smpo": 0,
                    "ivs": 1,
                    "ir": 0,
                    "hn": 1
                },
                "ec": [],
                "occ": {
                    "rurl": "",
                    "tcm": "",
                    "tsc": 0,
                    "ttp": 0,
                    "tlb": "",
                    "trb": ""
                },
                "ioph": "dfa77bfd325a"
            },
            "err": null
        }';

        // $client = new Client();
        // try {
        //     // Make a request to the external endpoint
        //     $response = $client->request('POST', 'https://api.gamesprime.fun/web-api/auth/session/v2/verifyOperatorPlayerSession', [
        //         'verify' => false, // Apenas para desenvolvimento! Remove ou define como true em produção.
        //         'headers' => [
        //             'Accept' => 'application/json',
        //         ],
        //     ]);

        //     // Get the body of the response
        //     $body = $response->getBody();

        //     // Return the JSON as a string
        //     return $body;
        // } catch (\Exception $e) {
        //     // Handle any exceptions and return an error message as JSON
        //     return json_encode(['error' => $e->getMessage()]);
        // }
    }
}
