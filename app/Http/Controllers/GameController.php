<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GameController extends Controller
{

    protected $cs;
    protected $ml;
    protected $bet;
    protected $table;
    protected $symbols;
    protected $multiplier;
    protected $lines;
    protected $sequence = [];

    public function getGameAll($game_id_code)
    {
        $filePath = storage_path('app/games-configs/allgames.json');

        if (!file_exists($filePath)) {
            return response()->json(["error" => "Arquivo allgames não encontrado"], 404);
        }

        $jsonContent = file_get_contents($filePath);
        $gameConfig = json_decode($jsonContent);

        foreach ($gameConfig->data as $game) {
            if (filter_var($game_id_code, FILTER_VALIDATE_INT) !== false) {
                if ($game->gameId == $game_id_code) {
                    return $game;
                }
            } else {
                if ($game->gameCode == $game_id_code) {
                    return $game;
                }
            }
        }

        return response()->json(["error" => "Jogo não foi encontrado"], 404);
    }


    public function verifySession(Request $request)
    {
        $update = $this->getGameAll($request["gi"]);

        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->rJSON(), true);

        $data["dt"]["geu"] = str_replace('name-game', $update->gameCode, $data["dt"]["geu"]);
        $data["dt"]["tk"] = $request->otk;
        // $data["dt"]["nkn"] = $request->       aqui login de usuario;
        $data["dt"]["gm"][0]["gid"] = $update->gameId;

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
    public function getGameInfo($game, Request $request)
    {
        $game = $this->getGameAll($game);

        $filePath = storage_path('app/game/' . $game->gameCode . '/getGameInfo.json');

        if (!file_exists($filePath)) {
            return response()->json(array('error' => 'Arquivo games não encontrado', 404));
        }

        $jsonContent = file_get_contents($filePath);
        $getGameInfo = json_decode($jsonContent);

        return response()->json($getGameInfo);
    }
    public function spin($game, Request $request)
    {
        $codes = explode('-', $game);
        $gameCode = '';
        foreach ($codes as $code) {
            $gameCode .= ucfirst($code);
        }

        $handle = $gameCode;
        $handle = '\App\Http\Games\PgSoft\\' . $gameCode;

        $this->cs = $request->cs;
        $this->ml = $request->ml;
        $this->bet = $this->cs * $this->ml;

        $gameInfo = new $handle();

        $this->table = $gameInfo->table;
        $this->symbols = $gameInfo->symbols;
        $this->multiplier = $gameInfo->multiplier;
        $this->lines = $gameInfo->lines;


        $this->generateResult();
        $calculatePrize = $this->calculatePrize();
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->spinJson(), true);
        $data["dt"]["si"]["orl"] = $this->sequence;
        $data["dt"]["si"]["rl"] = $this->sequence;


        $data["dt"]["si"]["cs"] = $this->cs;
        $data["dt"]["si"]["ml"] = $this->ml;

        $data["dt"]["si"]["tb"] = $this->bet * count($this->lines);
        $data["dt"]["si"]["tbb"] = $this->bet * count($this->lines);
        $data["dt"]["si"]["np"] = $this->bet * count($this->lines) + $calculatePrize["aw"] ?? 0;

        $data["dt"]["si"]["wp"] = $calculatePrize["wp"] ?? null;
        $data["dt"]["si"]["lw"] = $calculatePrize["lw"] ?? null;
        $data["dt"]["si"]["aw"] = $calculatePrize["aw"] ?? null;

        // Return the data as a JSON response
        return response()->json($data);
    }

    public function calculatePrize()
    {
        $aw = 0;
        $lw = null;
        $wp = null;

        if ($data = $this->checkWining()) {
            $lw = $data['lw'] ?? null;
            $wp = $data['wp'] ?? null;
            if ($lw) {
                foreach ($lw as $datalw) {
                    $aw += $datalw;
                }
            }
        }
        return [
            'aw' => $aw,
            'lw' => $lw,
            'wp' => $wp,
        ];
    }

    public function checkWining()
    {
        $winingLines = [];
        foreach ($this->lines as $index => $indices) {
            $wildCardCount = 0;
            $numbers = [];

            foreach ($indices as $i) {
                if (isset ($this->sequence[$i])) {
                    $numbers[] = $this->sequence[$i];
                    if ($this->sequence[$i] == 0) {
                        $wildCardCount++;
                    }
                }
            }

            $numbersCount = [];
            foreach ($numbers as $number) {
                if ($number != 0) {
                    $numbersCount[$number] = ($numbersCount[$number] ?? 0) + 1;
                }
            }

            $winingCounts = array_map(function ($count) use ($wildCardCount) {
                return $count + $wildCardCount;
            }, $numbersCount);
            if (!empty ($winingCounts)) {
                $maxWiningCount = max($winingCounts);
                if ($maxWiningCount >= 3) {
                    $winingNumber = array_search($maxWiningCount, $winingCounts);
                    if (isset ($this->multiplier[$winingNumber][$maxWiningCount])) {
                        $winingLines["wp"][$index] = $this->lines[$index];
                        $winingLines["lw"][$index] = $this->multiplier[$winingNumber][$maxWiningCount] * $this->bet;
                    }
                }
            } else {
                if ($wildCardCount == count($indices)) {
                    if (isset ($this->multiplier[0][3])) {
                        $winingLines["wp"][$index] = $this->lines[$index];
                        $winingLines["lw"][$index] = $this->multiplier[0][3] * $this->bet;
                    }
                }
            }
        }
        return $winingLines;
    }

    public function generateResult()
    {
        $count = count($this->symbols);

        for ($i = 0; $i < $this->table; $i++) {
            $randomIndex = mt_rand(0, $count - 1);
            $this->sequence[] = $this->symbols[$randomIndex];
        }
    }

    public function GetByResourcesTypeIds()
    {
        // Decode the JSON returned by the rJSON method
        $data = json_decode($this->GetByResourcesTypeIdsJson(), true);

        // Return the data as a JSON response
        return response()->json($data);
    }

    public function GetByResourcesTypeIdsJson()
    {
        return '{
            "dt": [
                {
                    "rid": 2,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/2/GemSaviour_168x168-ab06cffe.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 3,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/3/FortuneGods_168x168-3aff733d.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 6,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/6/Medusa2_168x168-2a9f180b.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 7,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/7/Medusa1_168x168-d4608fed.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 18,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/18/HoodWolf_168x168-843c442f.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 24,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/24/WinWinWon_168x168-913cf3ef.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 25,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/25/PlushieFrenzy_168x168-ab029c99.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 28,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/28/Hotpot_168x168-d59cd564.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 29,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/29/DragonLegend_168x168-91db6a15.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 33,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/33/HipHopPanda_168x168-15547bc6.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 34,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/34/LegendofHouYi_168x168-13f58e2b.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 35,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/35/Mr.Hallow_168x168-d9bf8dcf.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 36,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/36/ProsperityLion_168x168_-92038410.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 37,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/37/SantasGiftRush_168x168-c54bc748.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 38,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/38/GemSaviourSword_168x168-e0c2f395.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 39,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/39/PiggyGold_168x168-7c105c37.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 40,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/40/JungleDelight_168x168-5c2bb748.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 41,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/41/SymbolsofEgypt_168x168-29fa097f.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 42,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/42/GaneshaGold_168x168-cdedd446.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 44,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/44/EmperorsFavour_168x168-fea2651e.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 48,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/48/DoubleFortune_168x168-8e865d56.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 50,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/50/JourneytotheWealth_168x168-5eb1be65.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 53,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/53/TheGreatIcescape_168x168_-507c8898.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 54,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/54/CaptainsBounty_168x168-f50bc63d.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 59,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/59/NinjavsSamurai_168x168-e2a52085.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 60,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/60/LeprechaunRiches_168x168-0b05dc84.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 61,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/61/FlirtingScholar_168x168-03cb5d2d.png",
                    "l": "en-US",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 1,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/1/HoneyTrap_of_DiaoChan_168x168-b93b8e16.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 2,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/2/GemSaviour_168x168-ab06cffe.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 3,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/3/FortuneGods_168x168-3aff733d.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 6,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/6/Medusa2_168x168-2a9f180b.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 7,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/7/Medusa1_168x168-d4608fed.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:09"
                },
                {
                    "rid": 18,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/18/HoodWolf_168x168-843c442f.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 24,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/24/WinWinWon_168x168-913cf3ef.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 25,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/25/PlushieFrenzy_168x168-ab029c99.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 28,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/28/Hotpot_168x168-d59cd564.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 29,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/29/DragonLegend_168x168-91db6a15.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 33,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/33/HipHopPanda_168x168-15547bc6.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 34,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/34/LegendofHouYi_168x168-13f58e2b.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 35,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/35/Mr.Hallow_168x168-d9bf8dcf.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 36,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/36/ProsperityLion_168x168_-92038410.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 37,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/37/SantasGiftRush_168x168-c54bc748.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 38,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/38/GemSaviourSword_168x168-e0c2f395.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 39,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/39/PiggyGold_168x168-7c105c37.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 40,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/40/JungleDelight_168x168-5c2bb748.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 41,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/41/SymbolsofEgypt_168x168-29fa097f.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 42,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/42/GaneshaGold_168x168-cdedd446.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 44,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/44/EmperorsFavour_168x168-fea2651e.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 48,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/48/DoubleFortune_168x168-8e865d56.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 50,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/50/JourneytotheWealth_168x168-5eb1be65.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 53,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/53/TheGreatIcescape_168x168_-507c8898.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 54,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/54/CaptainsBounty_168x168-f50bc63d.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 59,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/59/NinjavsSamurai_168x168-e2a52085.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 61,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/61/FlirtingScholar_168x168-03cb5d2d.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 60,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/60/LeprechaunRiches_168x168-0b05dc84.png",
                    "l": "zh-CN",
                    "ut": "2019-09-27T10:57:10"
                },
                {
                    "rid": 62,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/62/GemSaviourConquest_168x168-3bff30bd.png",
                    "l": "zh-CN",
                    "ut": "2019-09-30T04:54:27"
                },
                {
                    "rid": 62,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/62/GemSaviourConquest_168x168-3bff30bd.png",
                    "l": "en-US",
                    "ut": "2019-09-30T04:54:27"
                },
                {
                    "rid": 64,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/64/MuayThai_168x168-8638e0c1.png",
                    "l": "zh-CN",
                    "ut": "2019-10-01T12:08:20"
                },
                {
                    "rid": 64,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/64/MuayThai_168x168-8638e0c1.png",
                    "l": "en-US",
                    "ut": "2019-10-01T12:08:21"
                },
                {
                    "rid": 63,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/63/DragonTigerLuck_168x168-5894f51d.png",
                    "l": "zh-CN",
                    "ut": "2019-10-03T08:07:13"
                },
                {
                    "rid": 63,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/63/DragonTigerLuck_168x168-5894f51d.png",
                    "l": "en-US",
                    "ut": "2019-10-03T08:07:13"
                },
                {
                    "rid": 65,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/65/MahjongWays_168x168-cc7e08cc.png",
                    "l": "zh-CN",
                    "ut": "2019-10-18T09:33:17"
                },
                {
                    "rid": 20,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/20/ReelLove_168x168-5038627d.png",
                    "l": "zh-CN",
                    "ut": "2019-11-22T04:42:03"
                },
                {
                    "rid": 20,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/20/ReelLove_168x168-5038627d.png",
                    "l": "en-US",
                    "ut": "2019-11-22T04:42:03"
                },
                {
                    "rid": 57,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/57/DragonHatch_168x168-456337e5.png",
                    "l": "zh-CN",
                    "ut": "2019-12-16T08:30:33"
                },
                {
                    "rid": 57,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/57/DragonHatch_168x168-456337e5.png",
                    "l": "en-US",
                    "ut": "2019-12-16T08:30:33"
                },
                {
                    "rid": 68,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/68/FortuneMouse_168x168-47dbb338.png",
                    "l": "zh-CN",
                    "ut": "2019-12-27T09:28:28"
                },
                {
                    "rid": 68,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/68/FortuneMouse_168x168-47dbb338.png",
                    "l": "en-US",
                    "ut": "2019-12-27T09:28:29"
                },
                {
                    "rid": 70,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/70/Candy Burst 168x168.png",
                    "l": "zh-CN",
                    "ut": "2020-02-13T09:58:37"
                },
                {
                    "rid": 70,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/70/Candy Burst 168x168.png",
                    "l": "en-US",
                    "ut": "2020-02-13T09:58:37"
                },
                {
                    "rid": 71,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/71/caishen-wins_168_168-86186b0c.png",
                    "l": "zh-CN",
                    "ut": "2020-02-19T02:50:48"
                },
                {
                    "rid": 71,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/71/caishen-wins_168_168-86186b0c.png",
                    "l": "en-US",
                    "ut": "2020-02-19T02:50:48"
                },
                {
                    "rid": 67,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/67/ShaolinSoccer_168x168-35282522.png",
                    "l": "zh-CN",
                    "ut": "2020-02-19T03:15:29"
                },
                {
                    "rid": 67,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/67/ShaolinSoccer_168x168-35282522.png",
                    "l": "en-US",
                    "ut": "2020-02-19T03:15:29"
                },
                {
                    "rid": 74,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/74/MahjongWaysTwo_168x168-1e5dbeee.png",
                    "l": "zh-CN",
                    "ut": "2020-03-06T08:37:45"
                },
                {
                    "rid": 74,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/74/MahjongWaysTwo_168x168-1e5dbeee.png",
                    "l": "en-US",
                    "ut": "2020-03-06T08:37:45"
                },
                {
                    "rid": 69,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/69/BikiniParadise_168x168-663109e3.png",
                    "l": "zh-CN",
                    "ut": "2020-03-19T09:46:20"
                },
                {
                    "rid": 73,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/73/EgyptsBook_168_168-6ff312b3.png",
                    "l": "zh-CN",
                    "ut": "2020-04-07T10:20:11"
                },
                {
                    "rid": 73,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/73/EgyptsBook_168_168-6ff312b3.png",
                    "l": "en-US",
                    "ut": "2020-04-07T10:20:11"
                },
                {
                    "rid": 75,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/75/GaneshaFortune_168_168-8c160aaa.png",
                    "l": "zh-CN",
                    "ut": "2020-04-14T06:57:08"
                },
                {
                    "rid": 75,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/75/GaneshaFortune_168_168-8c160aaa.png",
                    "l": "en-US",
                    "ut": "2020-04-14T06:57:08"
                },
                {
                    "rid": 82,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/82/SGS-e7840b27.png",
                    "l": "zh-CN",
                    "ut": "2020-05-28T02:37:39"
                },
                {
                    "rid": 82,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/82/SGS-e7840b27.png",
                    "l": "en-US",
                    "ut": "2020-05-28T02:37:39"
                },
                {
                    "rid": 79,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/79/SGS-ea3acc20.png",
                    "l": "zh-CN",
                    "ut": "2020-06-03T07:50:47"
                },
                {
                    "rid": 79,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/79/SGS-ea3acc20.png",
                    "l": "en-US",
                    "ut": "2020-06-03T07:50:47"
                },
                {
                    "rid": 83,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/83/SGS-215874f9.png",
                    "l": "zh-CN",
                    "ut": "2020-06-16T01:50:12"
                },
                {
                    "rid": 83,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/83/SGS-215874f9.png",
                    "l": "en-US",
                    "ut": "2020-06-16T01:50:12"
                },
                {
                    "rid": 85,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/85/SGS-b0340781.png",
                    "l": "zh-CN",
                    "ut": "2020-07-06T09:33:28"
                },
                {
                    "rid": 80,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/80/SGS-eab4819f.png",
                    "l": "zh-CN",
                    "ut": "2020-07-08T08:39:10"
                },
                {
                    "rid": 80,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/80/SGS-eab4819f.png",
                    "l": "en-US",
                    "ut": "2020-07-08T08:39:10"
                },
                {
                    "rid": 84,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/84/SGS-88a1b15b.png",
                    "l": "zh-CN",
                    "ut": "2020-07-17T02:34:13"
                },
                {
                    "rid": 84,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/84/SGS-88a1b15b.png",
                    "l": "en-US",
                    "ut": "2020-07-17T02:34:13"
                },
                {
                    "rid": 92,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/92/SGS-6814e138.png",
                    "l": "zh-CN",
                    "ut": "2020-07-24T03:40:00"
                },
                {
                    "rid": 69,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/69/BikiniParadise_168x168-663109e3.png",
                    "l": "en-US",
                    "ut": "2020-07-24T07:57:50"
                },
                {
                    "rid": 85,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/85/SGS-b0340781.png",
                    "l": "en-US",
                    "ut": "2020-07-27T11:08:59"
                },
                {
                    "rid": 65,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/65/MahjongWays_168x168-cc7e08cc.png",
                    "l": "en-US",
                    "ut": "2020-07-27T13:51:59"
                },
                {
                    "rid": 86,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/86/SGS-98a0a8a5.png",
                    "l": "zh-CN",
                    "ut": "2020-07-28T12:03:48"
                },
                {
                    "rid": 86,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/86/SGS-98a0a8a5.png",
                    "l": "en-US",
                    "ut": "2020-07-28T12:03:48"
                },
                {
                    "rid": 87,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/87/SGS-a63b7158.png",
                    "l": "zh-CN",
                    "ut": "2020-07-29T09:47:50"
                },
                {
                    "rid": 87,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/87/SGS-a63b7158.png",
                    "l": "en-US",
                    "ut": "2020-07-29T09:47:50"
                },
                {
                    "rid": 58,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/58/SGS-777a1211.png",
                    "l": "zh-CN",
                    "ut": "2020-08-07T08:05:18"
                },
                {
                    "rid": 58,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/58/SGS-777a1211.png",
                    "l": "en-US",
                    "ut": "2020-08-07T08:05:18"
                },
                {
                    "rid": 90,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/90/SGS-aa9b055c.png",
                    "l": "zh-CN",
                    "ut": "2020-08-21T07:12:00"
                },
                {
                    "rid": 90,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/90/SGS-aa9b055c.png",
                    "l": "en-US",
                    "ut": "2020-08-21T07:12:00"
                },
                {
                    "rid": 92,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/92/SGS-6814e138.png",
                    "l": "en-US",
                    "ut": "2020-09-01T03:51:33"
                },
                {
                    "rid": 93,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/93/SGS-b30b213e.png",
                    "l": "zh-CN",
                    "ut": "2020-09-17T03:34:59"
                },
                {
                    "rid": 93,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/93/SGS-b30b213e.png",
                    "l": "en-US",
                    "ut": "2020-09-17T03:34:59"
                },
                {
                    "rid": 88,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/88/SGS-0d34a88c.png",
                    "l": "zh-CN",
                    "ut": "2020-09-28T09:10:25"
                },
                {
                    "rid": 88,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/88/SGS-0d34a88c.png",
                    "l": "en-US",
                    "ut": "2020-09-28T09:10:25"
                },
                {
                    "rid": 97,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/97/SGS-bb7bb55f.png",
                    "l": "zh-CN",
                    "ut": "2020-09-29T07:32:56"
                },
                {
                    "rid": 97,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/97/SGS-bb7bb55f.png",
                    "l": "en-US",
                    "ut": "2020-09-29T07:32:56"
                },
                {
                    "rid": 94,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/94/SGS-0f58b776.png",
                    "l": "zh-CN",
                    "ut": "2020-09-29T07:33:48"
                },
                {
                    "rid": 94,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/94/SGS-0f58b776.png",
                    "l": "en-US",
                    "ut": "2020-09-29T07:33:48"
                },
                {
                    "rid": 101,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/101/SGS-cc58800d.png",
                    "l": "zh-CN",
                    "ut": "2020-10-08T08:03:23"
                },
                {
                    "rid": 101,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/101/SGS-cc58800d.png",
                    "l": "en-US",
                    "ut": "2020-10-08T08:03:23"
                },
                {
                    "rid": 98,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/98/SGS-1055ea51.png",
                    "l": "zh-CN",
                    "ut": "2020-10-09T07:08:00"
                },
                {
                    "rid": 98,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/98/SGS-1055ea51.png",
                    "l": "en-US",
                    "ut": "2020-10-09T07:08:00"
                },
                {
                    "rid": 102,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/102/SGS-91d7bdd3.png",
                    "l": "zh-CN",
                    "ut": "2020-10-12T09:13:27"
                },
                {
                    "rid": 102,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/102/SGS-91d7bdd3.png",
                    "l": "en-US",
                    "ut": "2020-10-12T09:13:27"
                },
                {
                    "rid": 103,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/103/SGS-e98163a9.png",
                    "l": "zh-CN",
                    "ut": "2020-10-14T03:09:00"
                },
                {
                    "rid": 100,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/100/SGS-21100faf.png",
                    "l": "zh-CN",
                    "ut": "2020-10-14T03:32:54"
                },
                {
                    "rid": 100,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/100/SGS-21100faf.png",
                    "l": "en-US",
                    "ut": "2020-10-14T03:32:54"
                },
                {
                    "rid": 89,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/89/SGS-9bd8e453.png",
                    "l": "zh-CN",
                    "ut": "2020-10-16T08:24:50"
                },
                {
                    "rid": 89,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/89/SGS-9bd8e453.png",
                    "l": "en-US",
                    "ut": "2020-10-16T08:24:50"
                },
                {
                    "rid": 95,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/95/SGS-8722e77b.png",
                    "l": "zh-CN",
                    "ut": "2020-10-20T10:39:00"
                },
                {
                    "rid": 95,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/95/SGS-8722e77b.png",
                    "l": "en-US",
                    "ut": "2020-10-20T10:39:00"
                },
                {
                    "rid": 91,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/91/SGS-e39408d8.png",
                    "l": "zh-CN",
                    "ut": "2020-10-28T08:24:12"
                },
                {
                    "rid": 91,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/91/SGS-e39408d8.png",
                    "l": "en-US",
                    "ut": "2020-10-28T08:24:12"
                },
                {
                    "rid": 105,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/105/SGS-27954eca.png",
                    "l": "zh-CN",
                    "ut": "2020-10-28T10:32:33"
                },
                {
                    "rid": 105,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/105/SGS-27954eca.png",
                    "l": "en-US",
                    "ut": "2020-10-28T10:32:33"
                },
                {
                    "rid": 104,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/104/SGS-6d855692.png",
                    "l": "zh-CN",
                    "ut": "2020-10-28T10:32:33"
                },
                {
                    "rid": 104,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/104/SGS-6d855692.png",
                    "l": "en-US",
                    "ut": "2020-10-28T10:32:33"
                },
                {
                    "rid": 106,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/106/SGS-ab10c5f2.png",
                    "l": "zh-CN",
                    "ut": "2020-11-09T07:35:04"
                },
                {
                    "rid": 106,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/106/SGS-ab10c5f2.png",
                    "l": "en-US",
                    "ut": "2020-11-09T07:35:04"
                },
                {
                    "rid": 107,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/107/SGS-1834c440.png",
                    "l": "zh-CN",
                    "ut": "2020-11-09T07:35:04"
                },
                {
                    "rid": 107,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/107/SGS-1834c440.png",
                    "l": "en-US",
                    "ut": "2020-11-09T07:35:04"
                },
                {
                    "rid": 108,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/108/SGS-466aef35.png",
                    "l": "zh-CN",
                    "ut": "2020-12-01T03:24:24"
                },
                {
                    "rid": 108,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/108/SGS-466aef35.png",
                    "l": "en-US",
                    "ut": "2020-12-01T03:24:24"
                },
                {
                    "rid": 103,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/103/SGS-e98163a9.png",
                    "l": "en-US",
                    "ut": "2021-01-11T04:04:58"
                },
                {
                    "rid": 112,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/112/SGS-0538c773.png",
                    "l": "zh-CN",
                    "ut": "2021-01-21T02:38:33"
                },
                {
                    "rid": 112,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/112/SGS-0538c773.png",
                    "l": "en-US",
                    "ut": "2021-01-21T02:38:33"
                },
                {
                    "rid": 113,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/113/SGS-1754f37d.png",
                    "l": "zh-CN",
                    "ut": "2021-02-02T07:31:26"
                },
                {
                    "rid": 113,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/113/SGS-1754f37d.png",
                    "l": "en-US",
                    "ut": "2021-02-02T07:31:26"
                },
                {
                    "rid": 114,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/114/SGS-01607869.png",
                    "l": "zh-CN",
                    "ut": "2021-02-22T02:50:58"
                },
                {
                    "rid": 114,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/114/SGS-01607869.png",
                    "l": "en-US",
                    "ut": "2021-02-22T02:50:58"
                },
                {
                    "rid": 115,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/115/SGS-e31625c2.png",
                    "l": "zh-CN",
                    "ut": "2021-03-18T11:28:30"
                },
                {
                    "rid": 115,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/115/SGS-e31625c2.png",
                    "l": "en-US",
                    "ut": "2021-03-18T11:28:30"
                },
                {
                    "rid": 117,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/117/SGS-561c405f.png",
                    "l": "zh-CN",
                    "ut": "2021-04-19T02:01:43"
                },
                {
                    "rid": 117,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/117/SGS-561c405f.png",
                    "l": "en-US",
                    "ut": "2021-04-19T02:01:43"
                },
                {
                    "rid": 118,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/118/SGS-5cafd100.png",
                    "l": "zh-CN",
                    "ut": "2021-04-19T02:01:43"
                },
                {
                    "rid": 118,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/118/SGS-5cafd100.png",
                    "l": "en-US",
                    "ut": "2021-04-19T02:01:43"
                },
                {
                    "rid": 119,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/119/SGS-e7513a2b.png",
                    "l": "zh-CN",
                    "ut": "2021-05-24T03:43:13"
                },
                {
                    "rid": 119,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/119/SGS-e7513a2b.png",
                    "l": "en-US",
                    "ut": "2021-05-24T03:43:13"
                },
                {
                    "rid": 120,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/120/SGS-87e1ffad.png",
                    "l": "zh-CN",
                    "ut": "2021-06-21T04:33:50"
                },
                {
                    "rid": 120,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/120/SGS-87e1ffad.png",
                    "l": "en-US",
                    "ut": "2021-06-21T04:33:50"
                },
                {
                    "rid": 121,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/121/SGS-4cfbe2a6.png",
                    "l": "zh-CN",
                    "ut": "2021-06-21T04:33:51"
                },
                {
                    "rid": 121,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/121/SGS-4cfbe2a6.png",
                    "l": "en-US",
                    "ut": "2021-06-21T04:33:51"
                },
                {
                    "rid": 122,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/122/SGS-86e447da.png",
                    "l": "zh-CN",
                    "ut": "2021-06-21T04:33:51"
                },
                {
                    "rid": 122,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/122/SGS-86e447da.png",
                    "l": "en-US",
                    "ut": "2021-06-21T04:33:51"
                },
                {
                    "rid": 110,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/110/SGS-7acae095.png",
                    "l": "zh-CN",
                    "ut": "2021-06-24T07:49:50"
                },
                {
                    "rid": 110,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/110/SGS-7acae095.png",
                    "l": "en-US",
                    "ut": "2021-06-24T07:49:50"
                },
                {
                    "rid": 125,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/125/SGS-0d3e34ba.png",
                    "l": "zh-CN",
                    "ut": "2021-07-22T03:25:26"
                },
                {
                    "rid": 125,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/125/SGS-0d3e34ba.png",
                    "l": "en-US",
                    "ut": "2021-07-22T03:25:26"
                },
                {
                    "rid": 1,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/1/HoneyTrap_of_DiaoChan_168x168-b93b8e16.png",
                    "l": "en-US",
                    "ut": "2021-08-02T13:02:17"
                },
                {
                    "rid": 126,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/126/SGS-5ebaee9a.png",
                    "l": "zh-CN",
                    "ut": "2021-08-24T10:16:12"
                },
                {
                    "rid": 130,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/lucky-piggy/SGS-c0b6b25e.png",
                    "l": "zh-CN",
                    "ut": "2021-12-30T08:08:56"
                },
                {
                    "rid": 130,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/lucky-piggy/SGS-c0b6b25e.png",
                    "l": "en-US",
                    "ut": "2021-12-30T08:08:56"
                },
                {
                    "rid": 128,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/legend-perseus/SGS-c2ebc3d7.png",
                    "l": "zh-CN",
                    "ut": "2022-01-03T02:40:52"
                },
                {
                    "rid": 128,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/legend-perseus/SGS-c2ebc3d7.png",
                    "l": "en-US",
                    "ut": "2022-01-03T02:40:52"
                },
                {
                    "rid": 124,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/124/SGS-070082d5.png",
                    "l": "zh-CN",
                    "ut": "2022-01-14T03:12:52"
                },
                {
                    "rid": 124,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/124/SGS-070082d5.png",
                    "l": "en-US",
                    "ut": "2022-01-14T03:12:52"
                },
                {
                    "rid": 123,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/123/SGS-170fb26d.png",
                    "l": "zh-CN",
                    "ut": "2022-03-02T04:42:37"
                },
                {
                    "rid": 123,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/123/SGS-170fb26d.png",
                    "l": "en-US",
                    "ut": "2022-03-02T04:42:37"
                },
                {
                    "rid": 129,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/win-win-fpc/SGS-a2c5e701.png",
                    "l": "zh-CN",
                    "ut": "2022-04-08T09:00:08"
                },
                {
                    "rid": 129,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/win-win-fpc/SGS-a2c5e701.png",
                    "l": "en-US",
                    "ut": "2022-04-08T09:00:08"
                },
                {
                    "rid": 127,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/speed-winner/SGS-e140cbef.png",
                    "l": "zh-CN",
                    "ut": "2022-05-30T03:11:11"
                },
                {
                    "rid": 127,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/speed-winner/SGS-e140cbef.png",
                    "l": "en-US",
                    "ut": "2022-05-30T03:11:11"
                },
                {
                    "rid": 132,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-coaster/SGS-3939262e.png",
                    "l": "zh-CN",
                    "ut": "2022-06-10T10:25:31"
                },
                {
                    "rid": 132,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-coaster/SGS-3939262e.png",
                    "l": "en-US",
                    "ut": "2022-06-10T10:25:31"
                },
                {
                    "rid": 135,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-bounty-sd/SGS-1625475e.png",
                    "l": "zh-CN",
                    "ut": "2022-06-21T08:53:46"
                },
                {
                    "rid": 135,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-bounty-sd/SGS-1625475e.png",
                    "l": "en-US",
                    "ut": "2022-06-21T08:53:46"
                },
                {
                    "rid": 1340277,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/asgardian-rs/SGS-37513a96.png",
                    "l": "zh-CN",
                    "ut": "2022-06-22T10:22:41"
                },
                {
                    "rid": 1312883,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/prosper-ftree/SGS-1d26f078.png",
                    "l": "zh-CN",
                    "ut": "2022-07-07T08:42:12"
                },
                {
                    "rid": 1312883,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/prosper-ftree/SGS-1d26f078.png",
                    "l": "en-US",
                    "ut": "2022-07-07T08:42:12"
                },
                {
                    "rid": 1338274,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/totem-wonders/SGS-74887bbd.png",
                    "l": "zh-CN",
                    "ut": "2022-07-13T03:29:58"
                },
                {
                    "rid": 1338274,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/totem-wonders/SGS-74887bbd.png",
                    "l": "en-US",
                    "ut": "2022-07-13T03:29:58"
                },
                {
                    "rid": 1418544,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/bakery-bonanza/SGS-5e2d74ba.png",
                    "l": "zh-CN",
                    "ut": "2022-08-17T10:07:49"
                },
                {
                    "rid": 1418544,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/bakery-bonanza/SGS-5e2d74ba.png",
                    "l": "en-US",
                    "ut": "2022-08-17T10:07:49"
                },
                {
                    "rid": 1372643,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/diner-delights/SGS-42fb6173.png",
                    "l": "zh-CN",
                    "ut": "2022-08-18T02:32:05"
                },
                {
                    "rid": 1372643,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/diner-delights/SGS-42fb6173.png",
                    "l": "en-US",
                    "ut": "2022-08-18T02:32:05"
                },
                {
                    "rid": 1368367,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/alchemy-gold/SGS-4f200843.png",
                    "l": "zh-CN",
                    "ut": "2022-10-04T02:49:18"
                },
                {
                    "rid": 1368367,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/alchemy-gold/SGS-4f200843.png",
                    "l": "en-US",
                    "ut": "2022-10-04T02:49:18"
                },
                {
                    "rid": 1381200,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/hawaiian-tiki/SGS-3173d9dd.png",
                    "l": "zh-CN",
                    "ut": "2022-10-18T09:33:58"
                },
                {
                    "rid": 1381200,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/hawaiian-tiki/SGS-3173d9dd.png",
                    "l": "en-US",
                    "ut": "2022-10-18T09:33:58"
                },
                {
                    "rid": 1402846,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/midas-fortune/SGS-b0cbf979.png",
                    "l": "zh-CN",
                    "ut": "2022-11-01T04:10:23"
                },
                {
                    "rid": 1402846,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/midas-fortune/SGS-b0cbf979.png",
                    "l": "en-US",
                    "ut": "2022-11-01T04:10:23"
                },
                {
                    "rid": 1420892,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/rave-party-fvr/SGS-7fae58b2.png",
                    "l": "zh-CN",
                    "ut": "2022-12-01T08:44:18"
                },
                {
                    "rid": 1420892,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/rave-party-fvr/SGS-7fae58b2.png",
                    "l": "en-US",
                    "ut": "2022-12-01T08:44:18"
                },
                {
                    "rid": 126,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/126/SGS-5ebaee9a.png",
                    "l": "en-US",
                    "ut": "2022-12-09T09:41:58"
                },
                {
                    "rid": 1543462,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fortune-rabbit/SGS-cb51bf17.png",
                    "l": "zh-CN",
                    "ut": "2023-01-06T09:50:36"
                },
                {
                    "rid": 1543462,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fortune-rabbit/SGS-cb51bf17.png",
                    "l": "en-US",
                    "ut": "2023-01-06T09:50:36"
                },
                {
                    "rid": 1340277,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/asgardian-rs/SGS-37513a96.png",
                    "l": "en-US",
                    "ut": "2023-03-09T09:14:08"
                },
                {
                    "rid": 26,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/26/TreeofFortune_168x168-631774c6.png",
                    "l": "en-US",
                    "ut": "2023-03-15T04:36:56"
                },
                {
                    "rid": 26,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/zh/SocialGameSmall/26/TreeofFortune_168x168-631774c6.png",
                    "l": "zh-CN",
                    "ut": "2023-03-15T04:37:46"
                },
                {
                    "rid": 1448762,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/songkran-spl/SGS-80716155.png",
                    "l": "zh-CN",
                    "ut": "2023-04-07T01:35:41"
                },
                {
                    "rid": 1448762,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/songkran-spl/SGS-80716155.png",
                    "l": "en-US",
                    "ut": "2023-04-07T01:35:41"
                },
                {
                    "rid": 1432733,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/myst-spirits/SGS-16c3227e.png",
                    "l": "zh-CN",
                    "ut": "2023-04-27T10:35:16"
                },
                {
                    "rid": 1432733,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/myst-spirits/SGS-16c3227e.png",
                    "l": "en-US",
                    "ut": "2023-04-27T10:35:16"
                },
                {
                    "rid": 1513328,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/spr-golf-drive/SGS-506e64f1.png",
                    "l": "zh-CN",
                    "ut": "2023-05-17T01:58:39"
                },
                {
                    "rid": 1513328,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/spr-golf-drive/SGS-506e64f1.png",
                    "l": "en-US",
                    "ut": "2023-05-17T01:58:39"
                },
                {
                    "rid": 1601012,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/lucky-clover/SGS-dc7a6b49.png",
                    "l": "zh-CN",
                    "ut": "2023-06-12T06:36:42"
                },
                {
                    "rid": 1601012,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/lucky-clover/SGS-dc7a6b49.png",
                    "l": "en-US",
                    "ut": "2023-06-12T06:36:42"
                },
                {
                    "rid": 1397455,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fruity-candy/SGS-52bc4515.png",
                    "l": "zh-CN",
                    "ut": "2023-07-10T10:05:15"
                },
                {
                    "rid": 1397455,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fruity-candy/SGS-52bc4515.png",
                    "l": "en-US",
                    "ut": "2023-07-10T10:05:15"
                },
                {
                    "rid": 1473388,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/cruise-royale/SGS-5d34cf10.png",
                    "l": "zh-CN",
                    "ut": "2023-08-08T07:04:16"
                },
                {
                    "rid": 1473388,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/cruise-royale/SGS-5d34cf10.png",
                    "l": "en-US",
                    "ut": "2023-08-08T07:04:16"
                },
                {
                    "rid": 1594259,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/safari-wilds/SGS-2649f83a.png",
                    "l": "zh-CN",
                    "ut": "2023-08-24T04:46:48"
                },
                {
                    "rid": 1594259,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/safari-wilds/SGS-2649f83a.png",
                    "l": "en-US",
                    "ut": "2023-08-24T04:46:48"
                },
                {
                    "rid": 1529867,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/ninja-raccoon/SGS-81d12e83.png",
                    "l": "zh-CN",
                    "ut": "2023-10-11T09:42:45"
                },
                {
                    "rid": 1529867,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/ninja-raccoon/SGS-81d12e83.png",
                    "l": "en-US",
                    "ut": "2023-10-11T09:42:45"
                },
                {
                    "rid": 1489936,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/ult-striker/SGS-44177ef3.png",
                    "l": "zh-CN",
                    "ut": "2023-10-11T09:42:46"
                },
                {
                    "rid": 1489936,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/ult-striker/SGS-44177ef3.png",
                    "l": "en-US",
                    "ut": "2023-10-11T09:42:46"
                },
                {
                    "rid": 1568554,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-heist-co/SGS-c6cd2748.png",
                    "l": "zh-CN",
                    "ut": "2023-10-30T16:03:47"
                },
                {
                    "rid": 1568554,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/wild-heist-co/SGS-c6cd2748.png",
                    "l": "en-US",
                    "ut": "2023-10-30T16:03:47"
                },
                {
                    "rid": 1555350,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/forge-wealth/SGS-b6c28d1e.png",
                    "l": "zh-CN",
                    "ut": "2023-11-15T06:54:10"
                },
                {
                    "rid": 1555350,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/forge-wealth/SGS-b6c28d1e.png",
                    "l": "en-US",
                    "ut": "2023-11-15T06:54:10"
                },
                {
                    "rid": 1580541,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/mafia-mayhem/SGS-1cdf4e86.png",
                    "l": "zh-CN",
                    "ut": "2023-12-05T05:03:01"
                },
                {
                    "rid": 1580541,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/mafia-mayhem/SGS-1cdf4e86.png",
                    "l": "en-US",
                    "ut": "2023-12-05T05:03:01"
                },
                {
                    "rid": 1655268,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/tsar-treasures/SGS-cbae2d00.png",
                    "l": "zh-CN",
                    "ut": "2023-12-12T03:06:35"
                },
                {
                    "rid": 1655268,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/tsar-treasures/SGS-cbae2d00.png",
                    "l": "en-US",
                    "ut": "2023-12-12T03:06:35"
                },
                {
                    "rid": 1615454,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/werewolf-hunt/SGS-3ffae844.png",
                    "l": "zh-CN",
                    "ut": "2023-12-27T04:37:11"
                },
                {
                    "rid": 1615454,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/werewolf-hunt/SGS-3ffae844.png",
                    "l": "en-US",
                    "ut": "2023-12-27T04:37:11"
                },
                {
                    "rid": 1451122,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/dragon-hatch2/SGS-8787a1fa.png",
                    "l": "zh-CN",
                    "ut": "2024-01-08T07:36:47"
                },
                {
                    "rid": 1451122,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/dragon-hatch2/SGS-8787a1fa.png",
                    "l": "en-US",
                    "ut": "2024-01-08T07:36:47"
                },
                {
                    "rid": 1695365,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fortune-dragon/SGS-85d8c240.png",
                    "l": "zh-CN",
                    "ut": "2024-01-18T08:32:27"
                },
                {
                    "rid": 1695365,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/fortune-dragon/SGS-85d8c240.png",
                    "l": "en-US",
                    "ut": "2024-01-18T08:32:27"
                },
                {
                    "rid": 1682240,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/cash-mania/SGS-ab3ac88a.png",
                    "l": "zh-CN",
                    "ut": "2024-03-11T08:09:07"
                },
                {
                    "rid": 1682240,
                    "rtid": 14,
                    "url": "https://public.pg-nmga.com/pages/static/image/en/SocialGameSmall/cash-mania/SGS-ab3ac88a.png",
                    "l": "en-US",
                    "ut": "2024-03-11T08:09:07"
                }
            ],
            "err": null
        }';
    }
    public function spinJson()
    {
        return '{
            "dt": {
                "si": {
                    "wc": 58,
                    "ist": false,
                    "itw": true,
                    "fws": 0,
                    "wp": null,
                    "orl": [
                        3,
                        2,
                        2,
                        6,
                        4,
                        7,
                        2,
                        6,
                        5
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
                        3,
                        2,
                        2,
                        6,
                        4,
                        7,
                        2,
                        6,
                        5
                    ],
                    "sid": "300001452017783",
                    "psid": "300001452017783",
                    "st": 1,
                    "nst": 1,
                    "pf": 1,
                    "aw": 0.00,
                    "wid": 0,
                    "wt": "C",
                    "wk": "0_C",
                    "wbn": null,
                    "wfg": null,
                    "blb": 23.60,
                    "blab": 23.20,
                    "bl": 23.20,
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
            "err": null
        }';
    }
    public function getGameRule()
    {
        return '{
            "dt": {
                "rtp": {
                    "Default": {
                        "min": 96.81,
                        "max": 96.81
                    }
                },
                "ows": {
                    "itare": false,
                    "tart": 0,
                    "igare": false,
                    "gart": 0
                },
                "jws": null
            },
            "err": null
        }';
    }

    public function getGameInfoJson()
    {
        return '';
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
                "geu": "game-api/name-game/",
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
