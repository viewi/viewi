<?php

function RenderAppComponentSlot7(AppComponent $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?>
                <?php
    $slotContents[0] = 'AppComponentSlot8';
    $pageEngine->renderComponent('HomePage', $component, $slotContents, ...$scope);
?>

            <?php   
}
