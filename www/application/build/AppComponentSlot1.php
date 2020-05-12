<?php

function RenderAppComponentSlot1(AppComponent $component, PageEngine $pageEngine)
{
    ?><?php $pageEngine->renderComponent('HomePage', $component, array (
  0 => 'AppComponentSlot2',
)); ?><?php
}
