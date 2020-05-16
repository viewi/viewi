<?php

function RenderUserItem(UserItem $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?><div>User details:</div>
<div>
    <?php
    $slotContents[0] = 'UserItemSlot15';
    $pageEngine->renderComponent($slots[0] ? $slots[0] : 'UserItemSlot15', $component, $slotContents, ...$scope);
?>
</div><?php   
}
