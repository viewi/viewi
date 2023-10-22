<?php

// php tools/locutus.php

use Viewi\Helpers;

require __DIR__ . '/../vendor/autoload.php';

$path = __DIR__ . '/../locutus/locutus/src/php';
$destination = __DIR__ . '/temp';
$template = file_get_contents(__DIR__ . '/function.template');
$listTemplate = file_get_contents(__DIR__ . '/list.template');

echo 'Locutus' . PHP_EOL;

$files = Helpers::collectFiles($path);

$stat = [];
$functions = [];
foreach ($files as $file => $_) {
    echo 'Processing: ' . $file . PHP_EOL;
    $nameParts = preg_split("/(\/|\\\)/", $file);
    $functionName = explode('.', array_pop($nameParts))[0];
    $className = pascalToCamelCase($functionName);
    if($functionName === 'echo') {
        $className = '_Echo';
    }
    if($functionName === 'empty') {
        $className = '_Empty';
    }
    if($functionName === 'isset') {
        $className = '_Isset';
    }
    $group = pascalToCamelCase(array_pop($nameParts));
    if ($functionName !== 'index') {
        $jsContent = file_get_contents($file);
        // ClassName
        // FuncName
        // /** DEPS */
        // FileName
        // Group
        $jsFileName = $className . '.js';

        $jsContent = preg_replace('/module.exports\s*=\s*/', '', $jsContent);
        $matches = [];
        preg_match_all("/.*(?<assignment>=\s*require\((?<path>[^)]*)('|\").*)/", $jsContent, $matches);
        $includes = [];
        if ($matches['assignment']) {
            foreach ($matches['assignment'] as $i => $content) {
                $jsContent = str_replace($matches[0][$i], '', $jsContent);
                $includeParts = explode('/', $matches['path'][$i]);
                $replacementName = $includeParts[count($includeParts) - 1];
                $replacementName = str_replace(['"', "'"], '', $replacementName);
                $includes[$replacementName] = 1;
                $stat[$replacementName][] = $matches[0][$i];
            }
        }
        print_r([$functionName, $className, $group]);

        $phpFunctionContent = str_replace('ClassName', $className, $template);
        $phpFunctionContent = str_replace('FuncName', $functionName, $phpFunctionContent);
        $phpFunctionContent = str_replace('/** DEPS */', implode(', ', array_map(fn ($a) => "'$a'", array_keys($includes))), $phpFunctionContent);
        $phpFunctionContent = str_replace('FileName', $jsFileName, $phpFunctionContent);
        $phpFunctionContent = str_replace('Group', $group, $phpFunctionContent);
        $folderPath = $destination . '/' . $group;
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $phpFilePath = $folderPath . '/' . $className . '.php';
        file_put_contents($phpFilePath, $phpFunctionContent);
        $jsFilePath = $folderPath . '/' . $jsFileName;
        file_put_contents($jsFilePath, $jsContent);
        $functions[$className] = [$group, $functionName];
    }
}

$listContent = $listTemplate;
$namespaces = '';
$items = '';
foreach ($functions as $className => [$group, $functionName]) {
    $namespaces .= "use Viewi\PhpJsFunctions\\$group\\$className;" . PHP_EOL;
    $items .= "    '$functionName' => $className::class," . PHP_EOL;
}
$listContent = str_replace('/** Namespaces */', $namespaces, $listContent);
$listContent = str_replace('/** ArrayItem */', $items, $listContent);
file_put_contents($destination . '/functions.php', $listContent);

print_r($stat);

function pascalToCamelCase($string, $capitalizeFirstCharacter = true)
{
    $str = str_replace(['_', '-'], '', ucwords($string, '_'));
    if (!$capitalizeFirstCharacter) {
        $str = lcfirst($str);
    }
    return $str;
}
