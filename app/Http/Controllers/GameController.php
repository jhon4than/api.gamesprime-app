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
        $client = new Client();
        try {
            // Make a request to the external endpoint
            $response = $client->request('POST', 'https://m.gamesprime.fun/web-api/auth/session/v2/verifyOperatorPlayerSession', [
                'verify' => false, // Apenas para desenvolvimento! Remove ou define como true em produção.
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            // Get the body of the response
            $body = $response->getBody();

            // Return the JSON as a string
            return $body;
        } catch (\Exception $e) {
            // Handle any exceptions and return an error message as JSON
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
