<?php

function RenderHomePageSlot10(HomePage $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>DefaultContent <?=htmlentities($component->title)?> <?php   
}
