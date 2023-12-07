<?php

namespace Viewi\Components\Assets;

use Exception;
use Viewi\Components\Attributes\PostBuildAction;
use Viewi\Components\BaseComponent;
use Viewi\Components\Config\ConfigService;
use Viewi\Components\Http\HttpClient;

#[PostBuildAction(CssBundlePostBuildAction::class)]
class CssBundle extends BaseComponent
{
    public function __construct(private ?ConfigService $config = null, private ?HttpClient $http = null)
    {
    }

    public array $links = [];
    public bool $minify = false;
    public bool $combine = false;
    public bool $inline = false;
    public bool $purge = false;
    public string $cssHtml = '<!--- CssBundle not initiated --->';

    public function mounted()
    {
        $baseUrl = $this->config->get('assetsUrl');
        if ($this->combine) {
            $cssBundleList = $this->config->get('cssBundle');
            $version = $this->version();
            if (!(isset($cssBundleList[$version]))) {
                throw new Exception('Css bundle not found');
            }
            $cssFile = $baseUrl . $cssBundleList[$version];
            if ($this->inline) {
                $this->cssHtml = "<style data-keep=\"$version\"> /** loading $cssFile **/ </style>";
                $this->http->get($cssFile)->then(
                    function (string $css) use ($version) {
                        $this->cssHtml = "<style data-keep=\"$version\">$css</style>";
                    },
                    function () use ($cssFile, $version) {
                        $this->cssHtml = "<style data-keep=\"$version\"> /** Error loading $cssFile **/ </style>";
                    }
                );
                return;
            }
            $this->cssHtml = "<link rel=\"stylesheet\" href=\"{$cssFile}\">";
        } else {
            $cssHtml = '';
            foreach ($this->links as $link) {
                $cssFile = $baseUrl . $link;
                $cssHtml .= "<link rel=\"stylesheet\" href=\"{$cssFile}\">";
                $this->cssHtml = $cssHtml;
            }
        }
    }

    public function version(): string
    {
        $key = implode('|', $this->links);
        $key .= $this->minify ? '1' : '0';
        $key .= $this->inline ? '1' : '0';
        $key .= $this->purge ? '1' : '0';
        $key .= $this->combine ? '1' : '0';
        return $key;
    }
}
