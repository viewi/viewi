<?php

include_once '../www/core/PageEngine/PageEngine.php';

class TagRenderingTest extends BaseTest
{
    private function TestComponent(
        string $component,
        string $path,
        string $expectedResultFile,
        UnitTestScope $T,
        bool $echoRendered = false
    ) {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render($component);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $path
            . DIRECTORY_SEPARATOR . $expectedResultFile);
        if ($echoRendered) {
            var_dump($html);
            // var_dump($expectetd);

            //    $r = str_split($html);
            //    $e = str_split($expectetd);
            //    for ($i = 0; $i < min(count($r), count($e)); $i++) {
            //        var_dump([$r[$i], $e[$i]]);

            //    }

        }
        $T->this($html)->equalsToHtml($expectetd);
    }

    public function CanRenderTag(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderTagComponent::class, 'CanRenderTag', 'CanRenderTag.expected.html', $T);
    }

    public function CanRenderRawHtml(UnitTestScope $T)
    {
        $this->TestComponent(RawHtmlComponent::class, 'CanRenderRawHtml', 'RawHtmlComponent.expected.html', $T);
    }

    public function CanRenderComponentTag(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderComponentTagComponent::class, 'CanRenderComponentTag', 'CanRenderComponentTag.expected.html', $T);
    }

    public function CanRenderSlots(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderSlotsComponent::class, 'CanRenderSlots', 'CanRenderSlots.expected.html', $T, false);
    }

    public function CanRenderAttributes(UnitTestScope $T)
    {
        $this->TestComponent(
            CanRenderAttributesComponent::class,
            'CanRenderAttributes',
            'CanRenderAttributes.expected.html',
            $T,
            false
        );
    }

    public function CanRenderDynamicTag(UnitTestScope $T)
    {
        $this->TestComponent(
            DynTestAppComponent::class,
            'CanRenderDynamicTag',
            'CanRenderDynamicTag.expected.html',
            $T,
            false
        );
    }

    public function CanRenderNamedSlots(UnitTestScope $T)
    {
        $this->TestComponent(
            NamedSlotsAppComponent::class,
            'CanRenderNamedSlots',
            'CanRenderNamedSlots.expected.html',
            $T,
            false
        );
    }

    public function CanPassAttributesAsComponentInputs(UnitTestScope $T)
    {
        $this->TestComponent(
            CanPassAttributesAsComponentInputsComponent::class,
            'CanPassAttributesAsComponentInputs',
            'CanPassAttributesAsComponentInputs.expected.html',
            $T,
            false
        );
    }

    public function CanRenderConditionalAttributes(UnitTestScope $T)
    {
        $this->TestComponent(
            ConditionalAttributesComponent::class,
            'CanRenderConditionalAttributes',
            'CanRenderConditionalAttributes.expected.html',
            $T,
            false
        );
    }

    public function ConditonalAndForeachRendering(UnitTestScope $T)
    {
        $this->TestComponent(
            ConditonalAndForeachRenderingComponent::class,
            'ConditonalAndForeachRendering',
            'ConditonalAndForeachRendering.expected.html',
            $T,
            false
        );
    }
}
