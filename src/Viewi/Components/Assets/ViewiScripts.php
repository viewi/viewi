<?php

namespace Viewi\Components\Assets;

use Viewi\BaseComponent;
use Viewi\Common\HttpClient;
use Viewi\Components\Services\AsyncStateManager;

class ViewiScripts extends BaseComponent
{
    private HttpClient $http;
    private AsyncStateManager $asyncStateManager;
    public string $responses = '{}';

    public function __construct(HttpClient $httpClient, AsyncStateManager $asyncStateManager)
    {
        $this->http = $httpClient;
        $this->asyncStateManager = $asyncStateManager;
        <<<javascript
        return; // data scripts only for backend
        javascript;
        $subscription = $this->asyncStateManager->subscribe('httpReady');
        $subscription->then(function () {
            $this->responses = json_encode($this->http->getScopeResponses());
        });
    }

    public function getDataScript()
    {
        <<<javascript
        return '<script>/** BLANK */</script>'; // data scripts only for backend
        javascript;
        return "<script>window.viewiScopeData = {$this->responses};</script>";
    }
}
