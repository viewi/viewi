<?php

function RenderAppComponentSlot6(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
                    <?php
$slotContents[] = 'AppComponentSlot7';
$pageEngine->renderComponent($component->dynamicTag, $component, $slotContents);
?>

                <?php   
}
