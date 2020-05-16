<?php

function RenderAppComponent_Slot7(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
                <?php
    $slotContents[0] = 'AppComponent_Slot8';
    $pageEngine->renderComponent('HomePage', $component, $slotContents, ...$scope);
?>

            <?php   
}
