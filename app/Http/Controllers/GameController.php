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

    public function getGameName()
    {
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->getGameJson(), true);

        // Return the data as a JSON response
        return response()->json($data);
    }
    public function getGameInfo()
    {
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->getGameInfoJson(), true);

        // Return the data as a JSON response
        return response()->json($data);
    }

    public function getGameInfoJson()
    {
        return '{
            "dt": {
                "fb": null,
                "wt": {
                    "mw": 5.0,
                    "bw": 20.0,
                    "mgw": 35.0,
                    "smgw": 50.0
                },
                "maxwm": null,
                "cs": [
                    0.08,
                    0.8,
                    3.0,
                    10.0
                ],
                "ml": [
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10
                ],
                "mxl": 5,
                "bl": 10.00,
                "inwe": false,
                "iuwe": false,
                "ls": {
                    "si": {
                        "wc": 50,
                        "ist": true,
                        "itw": true,
                        "fws": 0,
                        "wp": null,
                        "orl": [
                            7,
                            5,
                            4,
                            7,
                            6,
                            5,
                            2,
                            4,
                            7
                        ],
                        "lw": null,
                        "irs": false,
                        "gwt": -1,
                        "fb": null,
                        "ctw": 0.0,
                        "pmt": null,
                        "cwc": 0,
                        "fstc": null,
                        "pcwc": 0,
                        "rwsp": null,
                        "hashr": null,
                        "ml": 1,
                        "cs": 0.08,
                        "rl": [
                            7,
                            5,
                            4,
                            7,
                            6,
                            5,
                            2,
                            4,
                            7
                        ],
                        "sid": "300001446408686",
                        "psid": "300001446408686",
                        "st": 1,
                        "nst": 1,
                        "pf": 1,
                        "aw": 0.00,
                        "wid": 0,
                        "wt": "C",
                        "wk": "0_C",
                        "wbn": null,
                        "wfg": null,
                        "blb": 11.32,
                        "blab": 10.92,
                        "bl": 10.00,
                        "tb": 0.40,
                        "tbb": 0.40,
                        "tw": 0.00,
                        "np": -0.40,
                        "ocr": null,
                        "mr": null,
                        "ge": [
                            1,
                            11
                        ]
                    }
                },
                "cc": "BRL"
            },
            "err": null
        }';
    }

    public function getGameJson()
    {
        return '{
            "dt": {
                "1": "Honey Trap of Diao Chan",
                "2": "Gem Saviour",
                "3": "Fortune Gods",
                "6": "Medusa 2: The Quest of Perseus",
                "7": "Medusa 1: The Curse of Athena",
                "18": "Hood vs Wolf",
                "20": "Reel Love",
                "24": "Win Win Won",
                "25": "Plushie Frenzy",
                "26": "Tree of Fortune",
                "28": "Hotpot",
                "29": "Dragon Legend",
                "33": "Hip Hop Panda",
                "34": "Legend of Hou Yi",
                "35": "Mr. Hallow-Win",
                "36": "Prosperity Lion",
                "37": "Santas Gift Rush",
                "38": "Gem Saviour Sword",
                "39": "Piggy Gold",
                "40": "Jungle Delight",
                "41": "Symbols Of Egypt",
                "42": "Ganesha Gold",
                "44": "Emperors Favour",
                "48": "Double Fortune",
                "50": "Journey to the Wealth",
                "53": "The Great Icescape",
                "54": "Captains Bounty",
                "57": "Dragon Hatch",
                "58": "Vampires Charm",
                "59": "Ninja vs Samurai",
                "60": "Leprechaun Riches",
                "61": "Flirting Scholar",
                "62": "Gem Saviour Conquest",
                "63": "Dragon Tiger Luck",
                "64": "Muay Thai Champion",
                "65": "Mahjong Ways",
                "67": "Shaolin Soccer",
                "68": "Fortune Mouse",
                "69": "Bikini Paradise",
                "70": "Candy Burst",
                "71": "CaiShen Wins",
                "73": "Egypts Book of Mystery",
                "74": "Mahjong Ways 2",
                "75": "Ganesha Fortune",
                "79": "Dreams of Macau",
                "80": "Circus Delight",
                "82": "Phoenix Rises",
                "83": "Wild Fireworks",
                "84": "Queen of Bounty",
                "85": "Genies 3 Wishes",
                "86": "Galactic Gems",
                "87": "Treasures of Aztec",
                "88": "Jewels of Prosperity",
                "89": "Lucky Neko",
                "90": "Secrets of Cleopatra",
                "91": "Guardians of Ice & Fire",
                "92": "Thai River Wonders",
                "93": "Opera Dynasty",
                "94": "Bali Vacation",
                "95": "Majestic Treasures",
                "97": "Jack Frosts Winter",
                "98": "Fortune Ox",
                "100": "Candy Bonanza",
                "101": "Rise of Apollo",
                "102": "Mermaid Riches",
                "103": "Crypto Gold",
                "104": "Wild Bandito",
                "105": "Heist Stakes",
                "106": "Ways of the Qilin",
                "107": "Legendary Monkey King",
                "108": "Buffalo Win",
                "110": "Jurassic Kingdom",
                "112": "Oriental Prosperity",
                "113": "Raider Janes Crypt of Fortune",
                "114": "Emoji Riches",
                "115": "Supermarket Spree",
                "117": "Cocktail Nights",
                "118": "Mask Carnival",
                "119": "Spirited Wonders",
                "120": "The Queens Banquet",
                "121": "Destiny of Sun & Moon",
                "122": "Garuda Gems",
                "123": "Rooster Rumble",
                "124": "Battleground Royale",
                "125": "Butterfly Blossom",
                "126": "Fortune Tiger",
                "127": "Speed Winner",
                "128": "Legend of Perseus",
                "129": "Win Win Fish Prawn Crab",
                "130": "Lucky Piggy",
                "132": "Wild Coaster",
                "135": "Wild Bounty Showdown",
                "1312883": "Prosperity Fortune Tree",
                "1338274": "Totem Wonders",
                "1340277": "Asgardian Rising",
                "1368367": "Alchemy Gold",
                "1372643": "Diner Delights",
                "1381200": "Hawaiian Tiki",
                "1397455": "Fruity Candy",
                "1402846": "Midas Fortune",
                "1418544": "Bakery Bonanza",
                "1420892": "Rave Party Fever",
                "1432733": "Mystical Spirits",
                "1448762": "Songkran Splash",
                "1451122": "Dragon Hatch2",
                "1473388": "Cruise Royale",
                "1489936": "Ultimate Striker",
                "1513328": "Super Golf Drive",
                "1529867": "Ninja Raccoon Frenzy",
                "1543462": "Fortune Rabbit",
                "1555350": "Forge of Wealth",
                "1568554": "Wild Heist Cashout",
                "1580541": "Mafia Mayhem",
                "1594259": "Safari Wilds",
                "1601012": "Lucky Clover Lady",
                "1615454": "Werewolfs Hunt",
                "1655268": "Tsar Treasures",
                "1682240": "Cash Mania",
                "1695365": "Fortune Dragon"
            },
            "err": null
        }';
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
