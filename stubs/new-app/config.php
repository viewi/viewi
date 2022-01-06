<?php

use Viewi\PageEngine;

const VIEWI_COMPONENTS = __DIR__ . '/Components';
const PUBLIC_FOLDER = __DIR__ . '##public##';

return [
    PageEngine::SOURCE_DIR =>  VIEWI_COMPONENTS,
    PageEngine::SERVER_BUILD_DIR =>  __DIR__ . '/build',
    PageEngine::PUBLIC_ROOT_DIR => PUBLIC_FOLDER,
    PageEngine::DEV_MODE => true,
    PageEngine::RETURN_OUTPUT => true,
    PageEngine::COMBINE_JS => true
];
