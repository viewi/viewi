<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?>Recursion <?=htmlentities($component->title)?>

<?php
    $slotContents[0] = 'HomePageSlot14';
    $pageEngine->renderComponent($slots[0] ? $slots[0] : 'HomePageSlot14', $component, $slotContents, ...$scope);
?><?php   
}
