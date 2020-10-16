<?php

namespace Viewi;

class PageTemplate
{
    public ComponentInfo $ComponentInfo;
    public string $Path;
    public TagItem $RootTag;
    public string $PhpHtmlContent;
    public bool $ItsSlot = false;
}
