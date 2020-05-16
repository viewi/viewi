<?php

function RenderHomePage(
    HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?>Recursion <?=htmlentities($component->title)?>

<?php
    $slotContents[0] = 'HomePage_Slot14';
    $pageEngine->renderComponent($slots[0] ? $slots[0] : 'HomePage_Slot14', $component, $slotContents, ...$scope);
?><?php   
}
