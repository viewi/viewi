<?php

function RenderAppComponentSlot1(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    ?><?=htmlentities($component->content)?><?php
}
