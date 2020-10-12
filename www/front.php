<?php

declare(strict_types=1);

use Viewi\JsTranslator;

include 'core/Viewi/PageEngine.php';
include 'application/components/views/home/home.php';


$content = file_get_contents('application/components/views/posts/post.php');
$translator = new JsTranslator($content);

header("Content-type: text/text; charset=utf-8");
echo $translator->Convert();
