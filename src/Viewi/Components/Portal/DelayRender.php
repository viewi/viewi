<?php

namespace Viewi\Components\Portal;

use Viewi\Builder\Attributes\CustomJs;
use Viewi\Components\BaseComponent;
use Viewi\Components\Environment\Platform;
use Viewi\Components\Http\Message\Response;
use Viewi\Components\Render\IRenderable;
use Viewi\Components\Render\RenderContext;

#[CustomJs(false)]
class DelayRender extends BaseComponent implements IRenderable
{
    public function __construct(private Platform $platform) {}

    public function render(RenderContext $renderMeta): string
    {
        if (!isset($this->platform->runtimeState['delays'])) {
            $this->platform->runtimeState['delays'] = [];
        }
        $delayIndex = count($this->platform->runtimeState['delays']);
        $delayKey = "DELAY_{$delayIndex}_DELAY";
        $this->platform->runtimeState['delays'][$delayKey] = $renderMeta;
        $this->schedulePostAction();
        return $delayKey;
    }

    private function schedulePostAction()
    {
        $engine = $this->platform->engine();
        $actionId = 'delays';
        if (!$engine->postActionExists($actionId)) {
            $engine->schedulePostAction(
                function (Response $response) {
                    /**
                     * @var string
                     */
                    $body = $response->body;
                    $delays = $this->platform->runtimeState['delays'];
                    /**
                     * @var RenderContext $renderMeta
                     */
                    foreach ($delays as $delayKey => $renderMeta) {
                        $content = $this->platform->engine()->renderSlot(
                            $renderMeta->slots['component'],
                            $renderMeta->scope,
                            $renderMeta->slots['map']['default'],
                            $renderMeta->slots['parent']
                        );
                        $body = str_replace($delayKey, "<meta data-delayed-start>$content<meta data-delayed-end>", $body);
                    }
                    $response->body = $body;
                },
                $actionId
            );
        }
    }
}
