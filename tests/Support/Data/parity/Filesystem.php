<?php

// Parity cases for PhpJsFunctions/Filesystem - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'basename' => [
        ['/etc/sudoers.d'],
        ['/etc/sudoers.d', '.d'],
        ['/etc/'],
        ['.'],
        ['/'],
        ['a/b.txt', '.txt'],
    ],
    'dirname' => [
        ['/etc/passwd'],
        ['/etc/'],
        ['.'],
        ['/usr/local/lib', 2],
        ['file.txt'],
        ['/'],
    ],
    'pathinfo' => [
        ['/www/htdocs/inc/lib.inc.php'],
        ['/www/htdocs/inc/lib'],
        ['archive.tar.gz'],
        ['/www/htdocs/inc/lib.inc.php', 4],
    ],
];
