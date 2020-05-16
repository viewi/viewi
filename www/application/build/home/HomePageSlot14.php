<?php

function RenderHomePageSlot14(
    HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>DefaultContent <?=htmlentities($component->title)?> <?php   
}
