<?php

namespace Viewi\Components\Assets;

use Viewi\Builder\Attributes\Skip;
use Viewi\Builder\BuildAction\BuildActionItem;
use Viewi\Builder\BuildAction\IPostBuildAction;
use Viewi\Builder\Builder;

#[Skip]
class CssBundlePostBuildAction implements IPostBuildAction
{
    public function build(Builder $builder, array $props): ?BuildActionItem
    {
        $cssBundle = new CssBundle();
        $cssBundle->links = $props['links'] ?? [];
        $cssBundle->minify = $props['minify'] ?? false;
        $cssBundle->combine = $props['combine'] ?? false;
        $cssBundle->inline = $props['inline'] ?? false;
        $cssBundle->purge = $props['purge'] ?? false;
        $version = $cssBundle->version();
        $output = $cssBundle->combine && count($cssBundle->links) > 1
            ? '/' . crc32($version) . '.css'
            : $cssBundle->links[0];
        if ($cssBundle->minify || $cssBundle->purge) {
            $pathInfo = pathinfo($output, PATHINFO_ALL);
            $output = $pathInfo['dirname'] . $pathInfo['basename'];
            if ($cssBundle->purge) {
                $output .= '.pg';
            }
            if ($cssBundle->minify) {
                $output .= '.min';
            }
            $output .= '.css';
        }
        return new BuildActionItem('css', [
            'links' => $cssBundle->links,
            'minify' => $cssBundle->minify,
            'combine' => $cssBundle->combine,
            'inline' => $cssBundle->inline,
            'purge' => $cssBundle->purge,
            'version' => $version,
            'output' => $output
        ], [
            'cssBundle' => [$version => $output]
        ]);
    }
}
