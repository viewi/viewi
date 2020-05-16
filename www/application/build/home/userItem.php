<?php

function RenderUserItem(
    UserItem $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>USER: <?php
    $slotContents[0] = 'UserItem_Slot15';
    $pageEngine->renderComponent($slots[0] ? $slots[0] : 'UserItem_Slot15', $component, $slotContents, ...$scope);
?><?php   
}
