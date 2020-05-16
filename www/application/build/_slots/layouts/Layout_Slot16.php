<?php

function RenderLayout_Slot16(
    Layout $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>
        <title><?=htmlentities($component->title)?></title>
    <?php   
}
