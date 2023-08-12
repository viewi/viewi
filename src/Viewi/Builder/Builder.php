<?php

namespace Viewi\Builder;

use Viewi\Helpers;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\TemplateParser\TemplateParser;

class Builder
{
    private TemplateParser $templateParser;
    private JsTranspiler $jsTranspiler;
    private array $components;

    public function __construct()
    {
        $this->templateParser = new TemplateParser();
        $this->jsTranspiler = new JsTranspiler();
    }

    // collect files,
    // parse template,
    // transpile to js,
    // build php render script,
    // cache metadata (optional)
    // return metadata

    public function build(string $entryPath, array $includes = [])
    {
        $this->reset();
        // $includes will be shaken if not used in the $entryPath
        // 1. collect avaliable components
        // 2. transpile to js and collect uses, props, methods and paths
        // 3. parse html templates
        // 4. validate and build template:
        //      render function, 
        //      expressions,
        //      mark used components,
        //      collect reactivity deps
        // 5. cache metadata on each step if enabled
        // 6. return metadata
        $this->collectComponents($entryPath);
        foreach ($includes as $path) {
            $this->collectComponents($path, true);
        }
    }

    private function reset()
    {
        $this->components = [];
    }

    private function collectComponents(string $path, bool $shake = false)
    {
        $files = Helpers::collectFiles($path);
        foreach ($files as $filePath => $_) {
            $pathinfo = pathinfo($filePath);
            $extension = $pathinfo['extension'] ?? null;
            if ($extension === 'php') {
                // $pathinfo['filename'];
                $jsOutput = $this->jsTranspiler->convert(file_get_contents($filePath));
                Helpers::debug($jsOutput);
            }
            // switch ($extension) {
            //     case 'php': {
            //             $jsCode = $this->jsTranspiler->convert(file_get_contents($filePath));
            //         }
            //     case 'html': {
            //             $this->templateParser->parse(file_get_contents($filePath));
            //             break;
            //         }
            //     default:
            //         break;
            // }
        }
    }

    private function buildComponents()
    {
    }
}
