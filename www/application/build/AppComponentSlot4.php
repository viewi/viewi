<?php

function RenderAppComponentSlot4(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
            <?php
    $slotContents[0] = 'AppComponentSlot5';
    $pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

        <?php   
}
