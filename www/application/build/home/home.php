<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>Recursion <?=htmlentities($component->title)?>

<?php
$slotContents[0] = 'HomePageSlot10';
$pageEngine->renderComponent($slots[0] ? $slots[0] : 'HomePageSlot10', $component, $slotContents);
?><?php   
}
