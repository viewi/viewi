<?php

namespace Viewi\Components\Http;

use Viewi\DI\Singleton;

#[Singleton]
class WebsocketClient
{
    /**
     * 
     * @param string | URL $url 
     * @param string | string[] | null $protocols 
     * @return null|WebSocket 
     */
    public function getConnection($url = null, $protocols = null): ?WebSocket
    {
        <<<'javascript'
        const wsServer = url || 'wss://' + location.host + '/websocket';
        const websocket = new WebSocket(wsServer, protocols || undefined);
        return websocket;
        javascript;
        return null;
    }
}
