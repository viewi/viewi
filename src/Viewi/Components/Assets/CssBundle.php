<?php

namespace Viewi\Components\Assets;

use Viewi\Components\Attributes\PostBuildAction;
use Viewi\Components\BaseComponent;

#[PostBuildAction(CssBundlePostBuildAction::class)]
class CssBundle extends BaseComponent
{
    public array $links = [];
    public bool $minify = false;
    public bool $combine = false;
    public bool $inline = false;
    public bool $purge = false;

    public function getHtml(): string
    {
        $cssHtml = '';
        foreach ($this->links as $link) {
            $cssHtml .= "<link rel=\"stylesheet\" href=\"/viewi-endoaid{$link}\">";
        }
        return $cssHtml;
    }
}
