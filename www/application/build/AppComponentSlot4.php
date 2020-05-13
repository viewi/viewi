<?php

function RenderAppComponentSlot4(AppComponent $component, PageEngine $pageEngine)
{
    ?>
    <span>render inside <?=htmlentities($component->model)?></span>

<?php
}
