<?php

function RenderAppComponentSlot5(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
                <?php
$slotContents[] = 'AppComponentSlot6';
$pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

            <?php   
}
