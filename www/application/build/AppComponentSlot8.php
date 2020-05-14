<?php

function RenderAppComponentSlot8(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
            <span>render inside <?=htmlentities($component->model)?></span>
        <?php   
}
