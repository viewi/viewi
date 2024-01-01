<?php

namespace Viewi\Bridge;

use Viewi\App;
use Viewi\Components\Http\Message\Request;

class DefaultBridge implements IViewiBridge
{
    public function __construct(private App $viewiApp)
    {
    }

    public function file_exists(string $filename): bool
    {
        return file_exists($filename);
    }

    public function is_dir(string $filename): bool
    {
        return is_dir($filename);
    }

    public function file_get_contents(string $filename): string|false
    {
        return file_get_contents($filename);
    }

    public function request(Request $request): mixed
    {
        if ($request->isExternal) {
            return $this->externalRequest($request);
        }
        return $this->viewiApp->run($request->url, $request->method);
    }

    private function externalRequest(Request $request)
    {
        $curl = curl_init();
        $params = array(
            CURLOPT_URL => $request->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($request->method),
            CURLOPT_HTTPHEADER => $request->headers
        );
        if ($request->body != null) {
            $params[CURLOPT_HTTPHEADER]['Content-Type'] = 'application/json';
            $params[CURLOPT_POSTFIELDS] = json_encode($request->body);
        }
        curl_setopt_array($curl, $params);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
