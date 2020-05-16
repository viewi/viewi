<?php

function RenderAppComponent_Slot9(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?><?=htmlentities($component->content)?><?php   
}
