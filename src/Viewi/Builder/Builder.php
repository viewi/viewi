<?php

namespace Viewi\Builder;

use Exception;
use Viewi\ViewiPath;
use Viewi\Helpers;
use Viewi\JsTranspile\BaseFunction;
use Viewi\JsTranspile\ExportItem;
use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\JsTranspile\UseItem;
use Viewi\TemplateParser\TemplateParser;

class Builder
{
    private TemplateParser $templateParser;
    private JsTranspiler $jsTranspiler;
    /**
     * 
     * @var array<string, BuildItem>
     */
    private array $components;
    private array $avaliableComponents;
    /**
     * 
     * @var array<string, BaseFunction>
     */
    private array $avaliableFunctions;
    /**
     * 
     * @var array<string, BaseFunction>
     */
    private array $usedFunctions;
    // Config placeholders
    private bool $shakeTree = true;

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
        $this->avaliableComponents = [];
        $this->usedFunctions = [];
        $this->avaliableFunctions = require ViewiPath::dir() . DIRECTORY_SEPARATOR . 'JsTranspile' . DIRECTORY_SEPARATOR . 'functions.php';
        $this->collectComponents($entryPath, true);
        foreach ([...$includes, $this->getCoreComponentsPath()] as $path) {
            $this->collectComponents($path, !$this->shakeTree);
        }
        // 3. validate components and parse html templates
        // 4. validate and build template:
        //      render function, 
        //      expressions,
        //      mark used components,
        //      collect reactivity deps
        $this->templateParser->setAvaliableComponents(array_flip(array_keys($this->components)));
        foreach ($this->components as $buildItem) {
            $this->validateAndParseTemplate($buildItem);
        }
        // 5. cache metadata on each step if enabled
        // 6. return metadata
        Helpers::debug(array_flip(array_keys($this->components)));
        Helpers::debug($this->avaliableComponents);
        Helpers::debug($this->usedFunctions);
        Helpers::debug($this->components);
    }

    private function reset()
    {
        $this->components = [];
    }

    private function getCoreComponentsPath(): string
    {
        return ViewiPath::dir() . DIRECTORY_SEPARATOR . 'Components';
    }

    /**
     * 
     * @param JsOutput $jsOutput 
     * @param array<string, ExportItem> $exports 
     * @return void 
     */
    private function collectExports(JsOutput $jsOutput, array $exports, bool $include = false)
    {
        foreach ($exports as $exportItem) {
            if ($exportItem->Type === ExportItem::Namespace) {
                $this->collectExports($jsOutput, $exportItem->Children, $include);
            } elseif ($exportItem->Type === ExportItem::Class_) {
                $this->components[$exportItem->Name] = new BuildItem($exportItem->Name, $jsOutput, $include);
                $this->components[$exportItem->Name]->Uses = $jsOutput->getUses();
                if ($exportItem->Attributes !== null) {
                    if (isset($exportItem->Attributes['extends'])) {
                        $this->components[$exportItem->Name]->Extends = $exportItem->Attributes['extends'];
                    }
                    if (isset($exportItem->Attributes['namespace'])) {
                        $this->components[$exportItem->Name]->Namespace = $exportItem->Attributes['namespace'];
                    }
                }
            }
        }
    }

    private function collectComponents(string $path, bool $include = false)
    {
        $files = Helpers::collectFiles($path);
        foreach ($files as $filePath => $_) {
            $pathinfo = pathinfo($filePath);
            $extension = $pathinfo['extension'] ?? null;
            if ($extension === 'php') {
                $jsOutput = $this->jsTranspiler->convert(file_get_contents($filePath));
                $this->collectExports($jsOutput, $jsOutput->getExports(), $include);
                $templatePath = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '.html';
                if (is_file($templatePath) && isset($this->components[$pathinfo['filename']])) {
                    $this->components[$pathinfo['filename']]->TemplatePath = $templatePath;
                    $this->avaliableComponents[$pathinfo['filename']] = true;
                }
            }
        }
    }

    private function collectIncludes(BuildItem $buildItem)
    {
        foreach ($buildItem->Uses as $baseName => $useItem) {
            if ($useItem->Type === UseItem::Class_) {
                if (!$this->components[$baseName]->Include) {
                    $this->components[$baseName]->Include = true;
                    $this->collectIncludes($this->components[$baseName]);
                }
            } elseif ($useItem->Type === UseItem::Function) {
                $this->usedFunctions[$baseName] = $this->avaliableFunctions[$baseName];
            }
        }
    }

    private function validateAndParseTemplate(BuildItem $buildItem)
    {
        if (!$buildItem->Ready) {
            $buildItem->Ready = true;
            // 1. validate uses
            // 2. validate core functions
            foreach ($buildItem->Uses as $baseName => $useItem) {
                if ($useItem->Type === UseItem::Class_) {
                    if (!isset($this->components[$baseName])) {
                        $fullName = implode('\\', $useItem->Parts);
                        throw new Exception("Class '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                    }
                } elseif ($useItem->Type === UseItem::Function) {
                    if (!isset($this->avaliableFunctions[$baseName])) {
                        $fullName = implode('\\', $useItem->Parts);
                        throw new Exception("Function '$fullName' can not be found or is used outside of your source paths."); // TODO: create exception classes
                    }
                }
            }
            // 3. parse template if exists
            if ($buildItem->TemplatePath !== null) {
                $rootTag = $this->templateParser->parse(file_get_contents($buildItem->TemplatePath));
            }

            // 4. transpile and validate expressions
            if ($buildItem->Include) {
                $this->collectIncludes($buildItem);
            }
        }
    }
}
