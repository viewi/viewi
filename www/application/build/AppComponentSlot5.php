<?php

function RenderAppComponentSlot5(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
                <?php
$slotContents[0] = 'AppComponentSlot6';
$pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

            <?php   
}
