<?php

function RenderAppComponentSlot7(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?>
        <span>render inside <?=htmlentities($component->model)?></span>
    <?php
}
