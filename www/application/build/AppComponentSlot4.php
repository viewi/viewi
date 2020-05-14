<?php

function RenderAppComponentSlot4(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
            <?php
$slotContents[] = 'AppComponentSlot5';
$pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

        <?php   
}
