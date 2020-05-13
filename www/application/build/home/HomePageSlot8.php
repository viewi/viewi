<?php

function RenderHomePageSlot8(HomePage $component, PageEngine $pageEngine, array $slots)
{
    ?>DefaultContent <?=htmlentities($component->title)?> <?php
}
