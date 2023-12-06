<?php

namespace Viewi\Components\Assets;

use Exception;
use Viewi\Components\Attributes\PostBuildAction;
use Viewi\Components\BaseComponent;
use Viewi\Components\Config\ConfigService;

#[PostBuildAction(CssBundlePostBuildAction::class)]
class CssBundle extends BaseComponent
{
    public function __construct(private ?ConfigService $config = null)
    {
    }

    public array $links = [];
    public bool $minify = false;
    public bool $combine = false;
    public bool $inline = false;
    public bool $purge = false;

    public function getHtml(): string
    {
        $baseUrl = $this->config->get('assetsUrl');
        if ($this->combine) {
            $cssBundleList = $this->config->get('cssBundle');
            $version = $this->version();
            if (!(isset($cssBundleList[$version]))) {
                throw new Exception('Css bundle not found');
            }
            $cssFile = $baseUrl . $cssBundleList[$version];
            return "<link rel=\"stylesheet\" href=\"{$cssFile}\">";
        } else {
            $cssHtml = '';
            foreach ($this->links as $link) {
                $cssFile = $baseUrl . $link;
                $cssHtml .= "<link rel=\"stylesheet\" href=\"{$cssFile}\">";
            }
            return $cssHtml;
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
