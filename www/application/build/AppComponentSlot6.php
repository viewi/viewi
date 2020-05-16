<?php

function RenderAppComponentSlot6(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
            <?php
    $slotContents[0] = 'AppComponentSlot7';
    $pageEngine->renderComponent('HomePage', $component, $slotContents, ...$scope);
?>

        <?php   
}
