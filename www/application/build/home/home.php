<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine, array $slots)
{
    ?>Recursion <?=htmlentities($component->title)?>

<?php $pageEngine->renderComponent($slots[0] ? $slots[0] : 'HomePageSlot8', $component, []); ?><?php
}
