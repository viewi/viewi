<?php

function RenderAppComponentSlot2(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?><?=htmlentities($component->content)?><?php
}
