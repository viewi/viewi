<?php

// composer functions-doc: writes FUNCTIONS.md (see tests/Support/Parity/FunctionsDoc.php)

require __DIR__ . '/../vendor/autoload.php';
foreach (glob(__DIR__ . '/../tests/Support/Parity/*.php') as $file) {
    require_once $file;
}

$doc = \Tests\Support\Parity\FunctionsDoc::render();
file_put_contents(\Tests\Support\Parity\FunctionsDoc::PATH, $doc);
echo 'FUNCTIONS.md: ' . substr_count($doc, "\n| `") . " functions\n";
