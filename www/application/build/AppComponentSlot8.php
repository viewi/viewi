<?php

function RenderAppComponentSlot8(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
                    <?php
    $slotContents[0] = 'AppComponentSlot9';
    $pageEngine->renderComponent($component->dynamicTag, $component, $slotContents, ...$scope);
?>

                <?php   
}
