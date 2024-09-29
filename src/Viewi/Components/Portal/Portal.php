<?php

namespace Viewi\Components\Portal;

use Exception;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Components\BaseComponent;
use Viewi\Components\Environment\Platform;
use Viewi\Components\Http\Message\Response;
use Viewi\Components\Render\IRenderable;
use Viewi\Components\Render\RenderContext;
use Viewi\Helpers;

#[CustomJs(false)]
class Portal extends BaseComponent implements IRenderable
{
    public function __construct(private Platform $platform)
    {
    }

    public function render(RenderContext $renderMeta): string
    {
        if (isset($renderMeta->props['name'])) {
            if (!isset($this->platform->runtimeState['portals'])) {
                $this->platform->runtimeState['portals'] = [];
            }
            $portalName = $renderMeta->props['name'];
            /**
             * @var PortalScope
             */
            $portalScope = $this->platform->runtimeState['portals'][$portalName]
                ??
                ($this->platform->runtimeState['portals'][$portalName] = new PortalScope());
            if (
                $portalScope->defined
            ) {
                throw new Exception("Portal '$portalName' is already defined. Must be only one portal with such name.");
            }
            $portalScope->defined = true;
            $this->schedulePostAction();
            return "<i id=\"portal_{$portalName}\" style=\"display: none !important;\"></i>"
                . "PORTAL_$portalName"
                . "<i id=\"portal_{$portalName}_end\" style=\"display: none !important;\"></i>";
        } elseif (isset($renderMeta->props['to'])) {
            $portalName = $renderMeta->props['to'];
            /**
             * @var PortalScope
             */
            $portalScope = $this->platform->runtimeState['portals'][$portalName]
                ??
                ($this->platform->runtimeState['portals'][$portalName] = new PortalScope());
            $portalScope->contents[] = $this->platform->engine()->renderSlot(
                $renderMeta->slots['component'],
                $renderMeta->scope,
                $renderMeta->slots['map']['default'],
                $renderMeta->slots['parent']
            );
            $this->schedulePostAction();
            return '';
        }
        throw new Exception("Portal component should have either 'name' or 'to' attribute.");
    }

    private function schedulePostAction()
    {
        $engine = $this->platform->engine();
        $actionId = 'portals';
        if (!$engine->postActionExists($actionId)) {
            $engine->schedulePostAction(
                function (Response $response) {
                    /**
                     * @var string
                     */
                    $body = $response->body;
                    $portals = $this->platform->runtimeState['portals'];
                    // Helpers::debug($portals);
                    /**
                     * @var PortalScope $portalScope
                     */
                    foreach ($portals as $portalName => $portalScope) {
                        $portalPlaceholder = "PORTAL_{$portalName}";
                        $body = str_replace($portalPlaceholder, implode('', $portalScope->contents), $body);
                    }
                    $response->body = $body;
                },
                $actionId
            );
        }
    }
}
