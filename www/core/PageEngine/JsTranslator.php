<?php

namespace Vo;

use Exception;

class JsTranslator
{
    private string $phpCode;
    private int $position = 0;
    private int $length = 0;
    /** @var string[] */
    private array $parts;
    private string $jsCode = '';
    /** @var array<string,array<string,string>> */
    private array $scope;
    private int $scopeLevel = 0;
    private ?string $currentClass = null;
    private array $allowedOperators = [
        '+' => ['+', '+=', '++'], '-' => ['-', '-=', '--', '->'], '*' => ['*', '*=', '**', '*/'], '/' => ['/', '/=', '/*', '//'], '%' => ['%', '%='],
        '=' => ['=', '==', '===', '=>'], '!' => ['!', '!=', '!=='], '<' => ['<', '<=', '<=>', '<>'], '>' => ['>', '>='],
        'a' => ['and'], 'o' => ['or'], 'x' => ['xor'], '&' => ['&&'], '|' => ['||'],
        '.' => ['.', '.='], '?' => ['?', '??'], ':' => [':'], ')' => [')'], '{' => ['{'], '}' => ['}'], "'" => ["'"], '"' => ['"'],
        '[' => ['['], ']' => [']'], ',' => [','], '(' => ['(']
    ];
    /** array<string,array<string,string>>
     * [0 => '', 1 => ' ']
     * 0: spaces before, 1: spaces after
     */
    private array $spaces = array(
        '+' => array(0 => ' ', 1 => ' ',),
        '+=' => array(0 => ' ', 1 => ' ',),
        '++' => array(0 => '', 1 => '',),
        '-' => array(0 => ' ', 1 => ' ',),
        '-=' => array(0 => ' ', 1 => ' ',),
        '--' => array(0 => '', 1 => '',),
        '->' => array(0 => '', 1 => '',),
        '*' => array(0 => ' ', 1 => ' ',),
        '*=' => array(0 => ' ', 1 => ' ',),
        '**' => array(0 => ' ', 1 => ' ',),
        '/' => array(0 => ' ', 1 => ' ',),
        '/=' => array(0 => ' ', 1 => ' ',),
        '%' => array(0 => ' ', 1 => ' ',),
        '%=' => array(0 => ' ', 1 => ' ',),
        '=' => array(0 => ' ', 1 => ' ',),
        '==' => array(0 => ' ', 1 => ' ',),
        '===' => array(0 => ' ', 1 => ' ',),
        '!' => array(0 => ' ', 1 => '',),
        '!=' => array(0 => ' ', 1 => ' ',),
        '!==' => array(0 => ' ', 1 => ' ',),
        '<' => array(0 => ' ', 1 => ' ',),
        '<=' => array(0 => ' ', 1 => ' ',),
        '<=>' => array(0 => ' ', 1 => ' ',),
        '<>' => array(0 => ' ', 1 => ' ',),
        '>' => array(0 => ' ', 1 => ' ',),
        '>=' => array(0 => ' ', 1 => ' ',),
        'and' => array(0 => ' ', 1 => ' ',),
        'or' => array(0 => ' ', 1 => ' ',),
        'xor' => array(0 => ' ', 1 => ' ',),
        '&&' => array(0 => ' ', 1 => ' ',),
        '||' => array(0 => ' ', 1 => ' ',),
        '.' => array(0 => ' ', 1 => ' ',),
        '.=' => array(0 => ' ', 1 => ' ',),
        '?' => array(0 => ' ', 1 => ' ',),
        '??' => array(0 => ' ', 1 => ' ',),
        ':' => array(0 => ' ', 1 => ' ',),
        ')' => array(0 => '', 1 => '',),
        '{' => array(0 => ' ', 1 => ' ',),
        '}' => array(0 => ' ', 1 => ' ',),
        '(' => array(0 => '', 1 => '',),
        'return' => array(0 => ' ', 1 => ' ',),
        'new' => array(0 => '', 1 => ' ',),
    );
    private array $processors;
    private array $allowedSymbols = ['(' => true, ')' => true];
    private array $haltSymbols = ['(' => true, ')' => true, '{' => true, '}' => true, ';'];
    private array $phpKeywords = [
        '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break',
        'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue',
        'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
        'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
        'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global',
        'goto', 'if', 'implements', 'include', 'include_once', 'instanceof',
        'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or',
        'print', 'private', 'protected', 'public', 'require', 'require_once',
        'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
        'var', 'while', 'xor'
    ];

    private array $phpConstants = [
        '__CLASS__', '__DIR__', '__FILE__', '__FUNCTION__',
        '__LINE__', '__METHOD__', '__NAMESPACE__', '__TRAIT__'
    ];
    private ?string $lastBreak = null;
    private string $identation = '    ';
    private string $currentIdentation = '';
    /**
     * 
     * @var array<string,string>
     */
    private array $constructors = [];
    private bool $putIdentation = false;
    private ?string $buffer = null;
    private string $bufferIdentation = '';
    private bool $newVar = false;
    public function __construct(string $content)
    {
        $this->phpCode = $content;
        $this->parts = str_split($this->phpCode);
        $this->length = count($this->parts);
        $this->scope = [[]];
        $this->processors = [];
        foreach ($this->allowedOperators as $key => $operators) {
            $this->processors = array_merge($this->processors, array_flip($operators));
        }
        $this->processors = array_merge($this->processors, array_flip($this->phpKeywords));
        $this->processors = array_merge($this->processors, $this->allowedSymbols);
        // $spaces = [];
        // foreach ($this->processors as $key => $val) {
        //     $spaces[$key] = [0 => ' ', 1 => ' '];
        // }
        // $this->debug(var_export($spaces));
    }

    public function Convert(): string
    {
        $this->MatchPhpTag();
        try {
            while ($this->position < $this->length) {
                $this->jsCode .= $this->ReadCodeBlock();
            }
        } catch (Exception $exc) {
            $this->debug($exc->getMessage());
        }
        // $this->jsCode .= ' <PHP> ';
        while ($this->position < $this->length) {
            $this->jsCode .= $this->parts[$this->position];
            $this->position++;
        }

        $this->debug($this->jsCode);
        return $this->jsCode;
    }

    private function IsDeclared(string $varName): bool
    {
        foreach ($this->scope as $scope) {
            if (isset($scope[$varName])) {
                return true;
            }
        }
        return false;
    }

    private function ReadCodeBlock(...$breakOn): string
    {
        $code = '';
        $blocksLevel = 0;
        $lastIdentation = $this->currentIdentation;
        while ($this->position < $this->length) {
            $keyword = $this->MatchKeyword();
            if (!$keyword && $this->position === $this->length) {
                break;
            }
            // $this->debug($code);
            $identation = '';
            if ($this->putIdentation) {
                $this->putIdentation = false;
                $identation = $this->currentIdentation;
            }

            if (count($breakOn) > 0 && in_array($keyword, $breakOn)) {
                $this->lastBreak = $keyword;
                // $this->debug('Keyword Break: ' . $keyword);
                $this->position -= strlen($keyword);
                break;
            }
            $this->lastBreak = null;

            // $this->debug('Keyword: ' . $keyword);
            if ($keyword[0] === '$') {
                $varName = substr($keyword, 1);
                $this->buffer = $varName;
                $this->bufferIdentation = $identation;
                // $code .= $identation . $varName;
                // if ($keyword === '$this') {
                //     $expression = $this->ReadCodeBlock(...$breakOn + [';', ')']);
                //     // $this->debug($expression);
                //     $closing = $this->lastBreak === ';' ? '' : '';
                //     $code .= $identation . 'this' . $expression . $closing;
                //     $this->putIdentation = $closing !== '';
                // } else {
                //     $varName = substr($keyword, 1);
                //     $expression = $this->ReadCodeBlock(...$breakOn + [';', ')', '=']);
                //     if ($this->lastBreak === '=') {
                //         $expression .= $this->ReadCodeBlock(...$breakOn + [';', ')']);
                //         $varName = 'var '.$varName;
                //     }
                //     $closing = $this->lastBreak === ';' ? '' : '';
                //     $code .= $identation . $varName . $expression . $closing;
                //     $this->putIdentation = $closing !== '';
                // }
            } else if (ctype_digit($keyword)) {
                if ($this->buffer !== null) {
                    $code .= $this->bufferIdentation . $this->buffer;
                    $this->buffer = null;
                }
                $code .= $identation . $keyword;
                // $this->position++;
            } else {
                if ($keyword !== '=') {
                    if ($this->buffer !== null) {
                        $code .= $this->bufferIdentation . $this->buffer;
                        $this->buffer = null;
                    }
                    if ($this->newVar) {
                        $this->newVar = false;
                    }
                }
                switch ($keyword) {
                    case '=': {
                            if ($this->buffer !== null) {
                                // $this->debug($this->scope);
                                $isDeclared = $this->IsDeclared($this->buffer);
                                $varStatement = '';
                                if (!$isDeclared && $this->newVar) {
                                    $varStatement = 'var ';
                                    $this->scope[$this->scopeLevel][$this->buffer] = 'private';
                                }
                                $code .= $this->bufferIdentation
                                    . $varStatement
                                    . $this->buffer;

                                $this->buffer = null;
                            }
                            $code .= ' = ';
                            break;
                        }
                    case '->': {
                            $code .= '.';
                            break;
                        }
                    case '.': {
                            $code .= ' + ';
                            break;
                        }
                    case '.=': {
                            $code .= ' += ';
                            break;
                        }
                    case '{': {
                            $blocksLevel++;

                            $lastIdentation = $this->currentIdentation;
                            $this->currentIdentation .= $this->identation;
                            $code .= '{' . PHP_EOL . $this->currentIdentation;
                            // $this->debug('OPEN BLOCK ' . $keyword . $blocksLevel . '<<<====' . $code . '====>>>');
                            $this->newVar = true;
                            break;
                        }
                    case '}': {
                            $blocksLevel--;
                            if ($blocksLevel < 0) {
                                $this->position++;
                                // $this->debug('CLOSE BLOCK ' . $keyword . $blocksLevel . '<<<====' . $code . '====>>>');
                                break 2;
                            }
                            $this->currentIdentation = $lastIdentation;
                            $code .= $this->currentIdentation . '}' . PHP_EOL . $this->currentIdentation;
                            $this->newVar = true;
                            break;
                        }
                    case ';': {
                            $this->position++;
                            $code .= ';' . PHP_EOL;
                            $this->putIdentation = true;
                            $this->newVar = true;
                            break;
                        }
                    case '/*': {
                            $code .= $identation . $this->ReadMultiLineComment() . PHP_EOL;
                            $this->putIdentation = true;
                            break;
                        }
                    case '//': {
                            $code .= $identation . $this->ReadInlineComment() . PHP_EOL;
                            $this->putIdentation = true;
                            break;
                        }
                    case "'": {
                            // $this->debug($this->parts[$this->position] . ' ' . $keyword);
                            $code .= $identation . $this->ReadSingleQuoteString();
                            break;
                        }
                    case '"': {

                            $code .= $identation . $this->ReadDoubleQuoteString();
                            break;
                        }
                    case '[': {
                            $code .= $this->ReadArray(']');
                            break;
                        }
                    case 'array': {
                            $code .= $this->ReadArray(')');
                            break;
                        }
                    case 'use': {
                            $this->ProcessUsing();
                            break;
                        }
                    case 'namespace': {
                            $this->ProcessNamespace();
                            break;
                        }
                    case 'class': {
                            $code .= $identation . $this->ProcessClass();
                            break;
                        }
                    case 'for': {
                            $code .= $identation . $this->ReadFor();
                            break;
                        }
                    case 'foreach': {
                            $code .= $identation . $this->ReadForEach();
                            break;
                        }
                    case 'function':
                        $code .= $identation . $this->ReadFunction('public');
                        break;
                    case 'private':
                    case 'protected':
                    case 'public': {
                            $public = $keyword === 'public';
                            $typeOrName = $this->MatchKeyword();
                            if ($typeOrName === '?') {
                                $typeOrName = $this->MatchKeyword();
                            }
                            if ($typeOrName === 'function') {
                                $fn = $this->ReadFunction($keyword);
                                $code .= $identation . $fn;
                                break;
                            } else if ($typeOrName[0] === '$') {
                                $propertyName = substr($typeOrName, 1);
                                $code .= $identation . ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->scopeLevel][$propertyName] = $keyword;
                            } else {
                                // type
                                $name = $this->MatchKeyword();
                                $propertyName = substr($name, 1);
                                $code .= $identation . ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->scopeLevel][$propertyName] = $keyword;
                            }
                            $symbol = $this->MatchKeyword();
                            if ($symbol === '=') {
                                // match expression
                                $expression = $this->ReadCodeBlock(';');
                                $code .= " = $expression;" . PHP_EOL;
                            } else if ($symbol !== ';') {
                                throw new Exception("Unexpected symbol `$symbol` detected at ReadCodeBlock.");
                            } else {
                                $code .= ' = null;' . PHP_EOL;
                            }
                            $this->putIdentation = true;
                            $this->position++;
                            // $this->debug('********' . $code . '********');
                            break;
                        }
                    default:
                        // $this->debug($code);
                        // throw new Exception("Undefined keyword `$keyword` at ReadCodeBlock.");
                        if (isset($this->processors[$keyword])) {
                            $before = $identation;
                            $after = '';
                            if (isset($this->spaces[$keyword])) {
                                if ($identation === '') {
                                    $before = $this->spaces[$keyword][0];
                                }
                                $after = $this->spaces[$keyword][1];
                            }
                            $code .=  $before . $keyword . $after;
                            // $this->position++;
                        } else if (ctype_alnum(str_replace('_', '', $keyword))) {
                            $code .= $identation . $keyword;
                        } else {
                            $this->position++;
                            $code .= $identation . "'Undefined keyword `$keyword` at ReadCodeBlock.'";
                            break 2;
                        }
                }
            }
        }
        if ($this->buffer !== null) {
            $code .= $this->bufferIdentation . $this->buffer;
            $this->buffer = null;
        }
        return $code;
    }

    private function ReadMultiLineComment(): string
    {
        $comment = '/*';
        while ($this->position < $this->length) {
            if (
                $this->parts[$this->position]
                . $this->parts[$this->position + 1] === '*/'
            ) {
                $this->position += 2;
                break;
            }
            $comment .= $this->parts[$this->position];
            $this->position++;
        }
        return $comment . '*/';
    }

    private function ReadInlineComment(): string
    {
        $comment = '//';
        while ($this->position < $this->length) {
            if (
                $this->parts[$this->position] === "\n"
                || $this->parts[$this->position] === "\r"
            ) {
                break;
            }
            $comment .= $this->parts[$this->position];
            $this->position++;
        }
        return $comment;
    }

    private function ReadFor(): string
    {
        $loop = 'for ';
        $separator = '(';
        $this->SkipToTheSymbol('(');
        $this->newVar = true;
        while ($this->lastBreak !== ')') {
            $part = $this->ReadCodeBlock(';', ')', '(');
            $this->position++;
            $loop .= $separator . $part;
            $separator = '; ';
        }
        $loop .= ')';
        return $loop;
    }

    private function ReadForEach(): string
    {
        $loop = 'for ';
        $this->SkipToTheSymbol('(');
        $target = $this->ReadCodeBlock('as');
        $this->position += 2;
        $index = '_i';
        $var = $this->ReadCodeBlock('=>', ')');
        if ($this->lastBreak === '=>') {
            $this->position += 2;
            $index = $var;
            $var = $this->ReadCodeBlock(')');
        } else {
            $this->position += 1;
        }

        $loop .= "(var $index in $target) {" . PHP_EOL;
        $this->SkipToTheSymbol('{');
        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;
        $loop .= "{$this->currentIdentation}var $var = {$target}[{$index}];";
        $loop .= PHP_EOL;
        $this->putIdentation = true;
        $loop .= $this->ReadCodeBlock();
        $this->currentIdentation = $lastIdentation;
        $loop .= $this->currentIdentation . '}' . PHP_EOL;
        $this->putIdentation = true;
        return $loop;
    }

    private function ProcessClass(): string
    {

        $className = $this->MatchKeyword();
        $classHead = "var $className = function (";
        $option = $this->MatchKeyword();
        if ($option !== '{') {
            if ($option === 'extends') {
                $extends = $this->MatchKeyword();
                $this->debug('Extends: ' . $extends);
            }
            $this->SkipToTheSymbol('{');
        }
        $previousScope = $this->scope;
        $previousScopeLevel = $this->scopeLevel;

        $this->scopeLevel = 0;
        $this->scope = [[]];

        $lastClass = $this->currentClass;
        $this->currentClass = $className;

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;
        $this->putIdentation = true;
        $this->newVar = true;
        $classCode = $this->ReadCodeBlock();
        $this->newVar = true;
        $this->currentClass = $lastClass;
        $arguments = '';
        if (isset($this->constructors[$className])) {
            $classCode .= PHP_EOL . $this->currentIdentation . "this.__construct.apply(this,arguments);"
                . PHP_EOL;
            $arguments = $this->constructors[$className]['arguments'];
        }

        $classHead .= $arguments . ') ' . '{' . PHP_EOL . $classCode . '};' . PHP_EOL . PHP_EOL;
        $this->scope = $previousScope;
        $this->scopeLevel = $previousScopeLevel;
        $this->currentIdentation = $lastIdentation;

        // $this->debug('==========' . $classHead . '==========');
        return $classHead;
    }

    private function ReadFunction(string $modifier): string
    {
        $private = $modifier === 'private';

        $functionName = $this->MatchKeyword();
        $constructor = $functionName === '__construct';

        $functionCode = PHP_EOL . $this->currentIdentation . ($private ? 'var ' : 'this.')
            . $functionName . ' = function (';

        $this->scopeLevel++;
        $this->scope[$this->scopeLevel] = [];
        // read function arguments
        $arguments = $this->ReadArguments();
        $functionCode .= $arguments['arguments'] . ') ';

        // read function body
        $this->SkipToTheSymbol('{');

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;

        $this->putIdentation = true;
        $body = '';
        // default arguments
        $index = 0;
        foreach ($arguments['defaults'] as $name => $value) {
            if ($value !== false) {
                $body .= "{$this->currentIdentation}var $name = arguments.length > $index ? arguments[$index] : $value;" . PHP_EOL;
                $this->scope[$this->scopeLevel][$name] = 'private';
            }
            $index++;
        }
        $this->newVar = true;
        $body .= $this->ReadCodeBlock();
        $this->newVar = true;
        $this->scope[$this->scopeLevel] = null;
        unset($this->scope[$this->scopeLevel]);
        $this->scopeLevel--;

        $this->currentIdentation = $lastIdentation;

        $functionCode .= '{' . PHP_EOL . $body . $this->currentIdentation .  '};' . PHP_EOL;
        // $this->debug('==========' . $functionCode . '==========');
        if ($constructor) {
            $this->constructors[$this->currentClass] = $arguments;
        }
        return $functionCode;
    }

    private function ReadArguments(): array
    {
        $arguments = '';
        $this->SkipToTheSymbol('(');
        $object = [];
        $matchValue = false;
        $key = false;
        $value = false;

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;

        while ($this->position < $this->length) {
            $keyword = $this->MatchKeyword();
            if ($keyword === ')') {
                if ($key !== false) {
                    $object[$key] = $value;
                }
                break;
            } else if ($keyword === ',') {
                if ($key !== false) {
                    $object[$key] = $value;
                }
                $key = false;
                $value = false;
                $matchValue = false;
            } else if ($keyword === '?') {
                continue; // js doesn't have nullable
            } else if ($keyword === '=') {
                $value = $this->ReadCodeBlock(',', ')');
                // $this->debug('arg Val ' . $key . ' = '  . $value);
            } else {
                if ($keyword[0] === '$') {
                    $key = substr($keyword, 1);
                }
            }
        }
        $this->currentIdentation = $lastIdentation;
        $valueIdentation = $lastIdentation . $this->identation;
        // $this->debug($object);
        $totalLength = 0;
        foreach ($object as $key => $val) {
            $totalLength += $val === false ? strlen($key) + 2 : 0;
        }
        // $this->debug($totalLength);
        $newLineFormat = $totalLength > 90;

        $comma = $newLineFormat ? PHP_EOL . $valueIdentation : '';
        foreach ($object as $key => $value) {
            if ($value !== false) {
                continue;
            }
            $arguments .= $comma . $key;
            $comma = $newLineFormat ? ',' . PHP_EOL . $valueIdentation : ', ';
            $this->scope[$this->scopeLevel][$key] = 'private';
        }
        $arguments .= $newLineFormat ? PHP_EOL . $lastIdentation : '';
        return ['arguments' => $arguments, 'defaults' => $object];
    }

    private function ReadArray(string $closing): string
    {
        $elements = '';
        $object = [];
        $isObject = false;
        $index = 0;
        $key = $index;

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;

        while ($this->position < $this->length) {
            $item = $this->ReadCodeBlock(',', '=>', $closing, '(', ';');
            if ($this->lastBreak === ';') {
                break;
            }
            // $this->debug($this->lastBreak . ' ' . $item);
            // break;
            $this->position += strlen($this->lastBreak);
            if ($this->lastBreak === '=>') {
                // associative array, js object
                $isObject = true;
                $key = $item;
            } else if ($item !== '') {
                $escapedKey = $key;
                if (ctype_alnum(substr(str_replace('_', '', $key), 1, -1))) {
                    $escapedKey = substr($key, 1, -1);
                }
                $object[$escapedKey] = $item;

                if (is_int($key) || ctype_digit($key)) {
                    $key++;
                    $index = max($key, $index);
                    $key = $index;
                    // $this->debug($key . ' ' . $index);
                } else {
                    $key = $index;
                }
            }
            if ($this->lastBreak === $closing) {
                break;
            }
            continue;
        }

        $this->currentIdentation = $lastIdentation;
        $valueIdentation = $lastIdentation . $this->identation;
        // $this->debug($object);
        $totalLength = 0;
        foreach ($object as $key => $val) {
            $totalLength += ($isObject ? strlen($key) : 0) + strlen($val) + 2;
        }
        // $this->debug($totalLength);
        $newLineFormat = $totalLength > 90;
        if ($isObject) {
            $elements .= '{';
            $comma = $newLineFormat ? PHP_EOL . $valueIdentation : ' ';
            foreach ($object as $key => $value) {
                $elements .= $comma . $key . ': ' . $value;
                $comma = $newLineFormat ? ',' . PHP_EOL . $valueIdentation : ', ';
            }
            $elements .= count($object) > 0 ? ($newLineFormat ? PHP_EOL . $lastIdentation . '}'  : ' }') :  '}';
            return $elements;
        }
        if ($newLineFormat) {
            $elements = implode(',' . PHP_EOL . $valueIdentation, $object);
            return '[' . PHP_EOL . $valueIdentation . $elements . PHP_EOL . $lastIdentation . ']';
        } else {
            $elements = implode(', ', $object);
        }

        return "[$elements]";
    }

    private function ReadDoubleQuoteString(): string
    {
        $string = '';
        $skipNext = false;

        while ($this->position < $this->length) {
            if ($this->parts[$this->position] !== '"' || $skipNext) {
                if (!$skipNext && $this->parts[$this->position] === '\\') {
                    $skipNext = true;
                } else {
                    $skipNext = false;
                }
                $string .= $this->parts[$this->position];
            } else {
                $this->position++;
                break;
            }
            $this->position++;
        }
        return "\"$string\"";
    }

    private function ReadSingleQuoteString(): string
    {
        $string = '';
        $skipNext = false;

        while ($this->position < $this->length) {
            if ($this->parts[$this->position] !== "'" || $skipNext) {
                if (!$skipNext && $this->parts[$this->position] === '\\') {
                    $skipNext = true;
                } else {
                    $skipNext = false;
                }
                $string .= $this->parts[$this->position];
            } else {
                $this->position++;
                break;
            }
            $this->position++;
        }
        return "'$string'";
    }

    private function ProcessNamespace(): string
    {
        $this->SkipToTheSymbol(';');
        $this->newVar = true;
        return '';
    }

    private function ProcessUsing(): string
    {
        $this->SkipToTheSymbol(';');
        $this->newVar = true;
        return '';
    }

    private function SkipToTheKeyword(string $keyword)
    {
        while ($this->MatchKeyword() !== $keyword) {
            continue;
        }
    }

    private function SkipToTheSymbol(string $symbol): string
    {
        while ($this->position < $this->length) {
            if ($this->parts[$this->position] === $symbol) {
                $this->position++;
                break;
            }
            $this->position++;
        }
        return '';
    }

    private function MatchKeyword(): string
    {
        $keyword = '';
        $firstType = false;
        $operatorKey = false;
        while ($this->position < $this->length) {
            if (
                ctype_alnum($this->parts[$this->position])
                || $this->parts[$this->position] === '$'
                || $this->parts[$this->position] === '_'
            ) {
                if ($keyword === '') {
                    $firstType = ctype_digit($this->parts[$this->position]) ? 'number' : 'alpha';
                    // $this->debug($firstType . $this->parts[$this->position]);
                }
                if ($keyword !== '' && $firstType === 'operator') {
                    break;
                }
                if (
                    $keyword
                    && $firstType === 'number'
                    && !ctype_digit($this->parts[$this->position])
                ) {
                    break;
                }
                $keyword .= $this->parts[$this->position];
            } else if (!ctype_space($this->parts[$this->position])) {
                if ($keyword === '') {
                    $firstType = 'operator';
                    if (isset($this->allowedOperators[$this->parts[$this->position]])) {
                        $operatorKey = $this->parts[$this->position];
                    }
                }
                if ($keyword !== '' && $firstType !== 'operator') {
                    break;
                }
                if ($keyword !== '' && $operatorKey && !in_array(
                    $keyword . $this->parts[$this->position],
                    $this->allowedOperators[$operatorKey]
                )) {
                    // $this->position--;
                    // $this->debug('Move BACK' . $this->parts[$this->position - 1] . $this->parts[$this->position]);
                    break;
                }
                $keyword .= $this->parts[$this->position];
            } else {
                if ($keyword !== '') {
                    break;
                }
            }
            // if (isset($this->haltSymbols[$keyword])) {
            //     break;
            // }
            $this->position++;
        }
        // $this->debug($firstType . $keyword);
        return $keyword;
    }

    private function MatchPhpTag(): string
    {
        $openTagDetected = false;
        $questionMarkDetected = false;
        $pDetected = false;
        $hDetected = false;

        while ($this->position < $this->length) {
            // ========== <
            if ($this->parts[$this->position] === '<') {
                // possible php tag opening
                $openTagDetected = true;
            }

            // ========= <?
            if ($this->parts[$this->position] === '?') {
                if ($openTagDetected) {
                    $questionMarkDetected = true;
                }
            } else if ($this->parts[$this->position] !== '<') {
                $openTagDetected = false;
            }

            // ======== <?p or <?php
            if ($this->parts[$this->position] === 'p') {
                if ($questionMarkDetected) {
                    $pDetected = true;
                }
                if ($hDetected) { // <?php match
                    $this->position++;
                    break;
                }
            } else if ($this->parts[$this->position] !== '?') {
                $questionMarkDetected = false;
            }

            // ======== <?ph
            if ($this->parts[$this->position] === 'h') {
                if ($pDetected) {
                    $hDetected = true;
                }
            } else if ($this->parts[$this->position] !== 'p') {
                $pDetected = false;
            }


            $this->position++;
        }
        return '';
    }

    function debug($any, bool $checkEmpty = false): void
    {
        if ($checkEmpty && empty($any)) {
            return;
        }
        echo '<pre>';
        echo htmlentities(print_r($any, true));
        echo '</pre>';
    }
}
