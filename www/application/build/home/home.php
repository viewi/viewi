<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>Recursion <?=htmlentities($component->title)?>

<?php
$slotContents[] = 'HomePageSlot10';
$pageEngine->renderComponent($slots[count($slots) - 1] ? $slots[count($slots) - 1] : 'HomePageSlot10', $component, $slotContents);
?><?php   
}
