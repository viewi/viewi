<?php

namespace Viewi\Components\Assets;

use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class ViewiScripts extends BaseComponent
{
    private HttpClient $http;

    public function __construct(HttpClient $httpClient)
    {
        $this->http = $httpClient;
    }

    public function getDataScript()
    {
        <<<javascript
        return '<script>/** BLANK */</script>'; // data scripts only for backend
        javascript;
        $responses = json_encode($this->http->getScopeResponses());
        return "<script>window.viewiScopeData = $responses</script>";
    }
}
