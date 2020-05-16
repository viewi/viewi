<?php

function RenderHomePage_Slot14(
    HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>DefaultContent <?=htmlentities($component->title)?> <?php   
}
