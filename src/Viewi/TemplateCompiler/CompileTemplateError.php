<?php

namespace Viewi\TemplateCompiler;

use Exception;
use Viewi\TemplateParser\TagItem;

class CompileTemplateError extends Exception
{
    public ?TagItem $tagItem = null;
    public ?int $errorPosition = null;
    public ?int $errorLine = null;
    public ?int $errorEndPosition = null;
}
