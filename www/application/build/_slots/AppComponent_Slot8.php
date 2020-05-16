<?php

function RenderAppComponent_Slot8(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
                    <?php
    $slotContents[0] = 'AppComponent_Slot9';
    $pageEngine->renderComponent($component->dynamicTag, $component, $slotContents, ...$scope);
?>

                <?php   
}
