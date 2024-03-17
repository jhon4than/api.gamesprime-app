<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Import the File facade
use Illuminate\Support\Facades\Http; // Import the Http facade

class CopyController extends Controller
{
    public function copy($path)
    {
        $parts = explode('/', $path); // Removido o named argument 'separator'
        $parts[0] .= '-2'; // Concatenação simplificada
        $adjustedPath = implode("/", $parts); // Removido o named argument 'separator'
        $localPath = public_path($adjustedPath);

        if (File::exists($localPath)) {
            $extension = pathinfo($localPath, PATHINFO_EXTENSION); // Removido o named argument 'flags'
            $mimeTypes = [
                'json' => 'application/json',
                'js' => 'text/javascript',
                'html' => 'text/html'
            ];

            $contentType = $mimeTypes[$extension] ?? 'text/plain';
            $fileContents = File::get($localPath);
            // Adicionada verificação para o corpo da resposta não ser nulo
            return response($fileContents ?? '', 200, ['Content-Type' => $contentType]);

        } else {
            // Corrigido para aspas duplas e interpolação correta da variável $path
            $response = Http::get("https://static.pg-nmga.com/{$path}");

            if ($response->successful()) {
                $directoryPath = dirname($localPath);
                if (!File::exists($directoryPath)) {
                    File::makeDirectory($directoryPath, 0755, true, true); // Removido os named arguments
                }

                File::put($localPath, $response->body());
                $extension = pathinfo($localPath, PATHINFO_EXTENSION); // Removido o named argument 'flags'
                $mimeTypes = [
                    'json' => 'application/json',
                    'js' => 'text/javascript',
                    'html' => 'text/html'
                ];
    
                $contentType = $mimeTypes[$extension] ?? 'text/plain';
    
                // Adicionada verificação para o corpo da resposta não ser nulo
                return response($response->body() ?? '', 200, ['Content-Type' => $contentType]);
            }else{
                return response('Arquivo não existe ou está fora do ar', 404);
            }

        }
    }
}
