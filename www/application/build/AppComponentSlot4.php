<?php

function RenderAppComponentSlot4(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?>
    <span>render inside <?=htmlentities($component->model)?></span>
<?php
}
