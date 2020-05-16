<?php

function RenderAppComponent_Slot6(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
            <?php
    $slotContents[0] = 'AppComponent_Slot7';
    $pageEngine->renderComponent('HomePage', $component, $slotContents, ...$scope);
?>

        <?php   
}
