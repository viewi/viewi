<?php

function RenderHomePage(HomePage $component, PageEngine $pageEngine)
{
    ?><div>
    <?=htmlentities($component->title)?>

</div><?php
}
