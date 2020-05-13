<?php

function RenderAppComponentSlot1(AppComponent $component, PageEngine $pageEngine)
{
    ?><?=htmlentities($component->content)?><?php
}
