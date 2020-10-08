<?php

namespace Vo;

use Exception;
use ReflectionClass;

require 'JsFunctions/export.php';

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
    private ?string $currentMethod = null;
    private array $allowedOperators = [
        '+' => ['+', '+=', '++'], '-' => ['-', '-=', '--', '->'], '*' => ['*', '*=', '**', '*/'], '/' => ['/', '/=', '/*', '//'],
        '%' => ['%', '%='], '=' => ['=', '==', '===', '=>'], '!' => ['!', '!=', '!=='],
        '<' => ['<', '<=', '<=>', '<>', '<<<'], '>' => ['>', '>='],
        'a' => ['and'], 'o' => ['or'], 'x' => ['xor'], '&' => ['&&'], '|' => ['||'],
        '.' => ['.', '.='], '?' => ['?', '??'], ':' => [':'], ')' => [')'], '{' => ['{'], '}' => ['}'], "'" => ["'"], '"' => ['"'],
        '[' => ['[', '[]'], ']' => [']'], ',' => [','], '(' => ['(']
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
        '<<<' => array(0 => '', 1 => '',),
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
        ':' => array(0 => '', 1 => ' ',),
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
    public $lastKeyword = '';
    private bool $thisMatched = false;
    private ?string $callFunction = null;

    private static bool $functionConvertersInited = false;
    /**
     * 
     * @var array<string,BaseFunctionConverter> $functionConverters
     */
    private static array $functionConverters = [];
    /** @var array<string,string[]> */
    private array $variablePaths;
    private string $currentVariablePath;
    private bool $collectVariablePath;
    private bool $skipVariableKey;
    private bool $expressionScope = false;
    private string $latestSpaces;
    public string $latestVariablePath;
    private $pasteArrayReactivity;
    public function __construct(string $content)
    {
        if (!self::$functionConvertersInited) {
            self::$functionConvertersInited = true;
            $types = get_declared_classes();
            foreach ($types as $class) {
                /** @var BaseFunctionConverter $class */
                if (is_subclass_of($class, BaseFunctionConverter::class)) {
                    self::$functionConverters[$class::$name] = $class;
                }
            }
            // $this->debug(self::$functionConverters);
        }
        $this->phpCode = $content;
        $this->Reset();
        $this->processors = [];
        foreach ($this->allowedOperators as $key => $operators) {
            $this->processors = array_merge($this->processors, array_flip($operators));
        }
        // $this->processors = array_merge($this->processors, array_flip($this->phpKeywords));
        $this->processors = array_merge($this->processors, $this->allowedSymbols);
        // $spaces = [];
        // foreach ($this->processors as $key => $val) {
        //     $spaces[$key] = [0 => ' ', 1 => ' '];
        // }
        // $this->debug(var_export($spaces));
    }

    private function Reset()
    {
        $this->lastBreak = null;
        $this->lastKeyword = '';
        $this->jsCode = '';
        $this->position = 0;
        $this->parts = str_split($this->phpCode);
        $this->length = count($this->parts);
        $this->scope = [[]];
        $this->callFunction = null;
        $this->variablePaths = [];
        $this->currentVariablePath = '';
        $this->collectVariablePath = false;
        $this->skipVariableKey = false;
        $this->currentMethod = null;
        $this->currentClass = null;
        $this->latestVariablePath = '';
        $this->pasteArrayReactivity = false;
    }

    public function ActivateReactivity(array $info)
    {
        $this->pasteArrayReactivity = $info;
    }

    public function GetVariablePaths(): array
    {
        return $this->variablePaths;
    }

    public function GetKeywords(?string $content = null): array
    {
        $keywords = [
            [], // keyword
            [] // space before
        ];
        if ($content !== null) {
            $this->phpCode = $content;
            $this->Reset();
        }
        $keyword = $this->NextKeyword();
        while ($keyword !== null) {
            $keywords[0][] = $keyword;
            $keywords[1][] = $this->latestSpaces;
            $keyword = $this->NextKeyword();
        }
        return $keywords;
    }

    public function NextKeyword(): ?string
    {
        $keyword = $this->MatchKeyword();
        // $this->debug($keyword);
        if ($keyword === '' && $this->position === $this->length) {
            return null;
        }
        return $keyword;
    }

    public function Convert(?string $content = null, bool $skipPhpTag = false): string
    {
        if ($content !== null) {
            $this->phpCode = $content;
            $this->Reset();
        }
        if (!$skipPhpTag) {
            $this->MatchPhpTag();
        }
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
        $this->StopCollectingVariablePath();
        // $this->debug($this->jsCode);
        return $this->jsCode;
    }

    private function IsDeclared(string $varName): ?string
    {
        foreach ($this->scope as $scope) {
            if (isset($scope[$varName])) {
                return $scope[$varName];
            }
        }
        return null;
    }

    private function GetVariable(bool $thisMatched): string
    {
        $code = '';
        if ($this->buffer !== null) {
            $declaredProp = $this->IsDeclared($this->buffer);
            $varStatement = '';
            if ($thisMatched) {
                if ($declaredProp === null) {
                    $this->scope[$this->scopeLevel][$this->buffer] = 'public';
                    $this->scope[$this->scopeLevel][$this->buffer . '_this'];
                    $varStatement = 'this.';
                } else {
                    $varStatement = $declaredProp === 'private' ? '' : 'this.';
                }
                $this->latestVariablePath = $varStatement;
                // $this->debug('VAR: ' . $varStatement);
            } else if ($this->newVar) {
                if ($declaredProp === null) {
                    $this->scope[$this->scopeLevel][$this->buffer] = 'private';
                    $varStatement = 'var ';
                } else {
                    $declaredThis = $this->IsDeclared($this->buffer . '_this');
                    if ($declaredThis !== null) {
                        // conflict, need to replace var name
                        $this->scope[$this->scopeLevel][$this->buffer . '_replace'] = 'private';
                        $i = 0;
                        $newName = $this->buffer . '_' . $i;
                        while ($this->IsDeclared($newName . '_this') !== null) {
                            $i++;
                            $newName = $this->buffer . '_' . $i;
                        }
                        $this->scope[$this->scopeLevel][$this->buffer . '_replace'] = $newName;
                        $this->scope[$this->scopeLevel][$newName] = 'private';
                        $this->buffer = $newName;
                        $varStatement = 'var ';
                    }
                }
                $this->latestVariablePath = '';
                // else {
                //     $varStatement = $declaredProp === 'private' ? '' : 'this.';
                // }
            } else {
                $replace = $this->IsDeclared($this->buffer . '_replace');
                if ($replace !== null) {
                    $this->buffer = $replace;
                }
            }
            // $this->debug($this->buffer);
            $code .= $this->bufferIdentation
                . $varStatement
                . $this->buffer;
            $this->latestVariablePath .= $this->buffer;
            // $this->debug($varStatement . $this->buffer);
            $this->buffer = null;
        }

        return $code;
    }

    public function setOuterScope()
    {
        $this->expressionScope = true;
    }

    public function StopCollectingVariablePath(): void
    {
        if ($this->collectVariablePath) {
            $this->collectVariablePath = false;
            if ($this->currentVariablePath !== '') {
                $class = $this->currentClass ?? 'global';
                $method = $this->currentMethod ?? 'function';
                if (!isset($this->variablePaths[$class])) {
                    $this->variablePaths[$class] = [];
                }
                if (!isset($this->variablePaths[$class][$method])) {
                    $this->variablePaths[$class][$method] = [];
                }
                $this->variablePaths[$class][$method][$this->currentVariablePath] = true;
                $this->currentVariablePath = '';
            }
        }
    }

    public function ReadCodeBlock(...$breakOnConditios): string
    {
        //BreakCondition
        $breakConditions = [];
        $breakOn = array_reduce(
            $breakOnConditios,
            function ($a, $item) use (&$breakConditions) {
                if (is_string($item)) {
                    $a[] = $item;
                } else if ($item instanceof BreakCondition) {
                    if ($item->Keyword !== null) {
                        $a[] = $item->Keyword;
                        $breakConditions[$item->Keyword] = $item;
                    }
                }
                return $a;
            },
            []
        );
        // $this->debug($breakOn);
        // $this->debug($breakConditions);
        $code = '';
        $blocksLevel = 0;
        $lastIdentation = $this->currentIdentation;
        $thisMatched = false;
        $parenthesisNormal = 0;
        while ($this->position < $this->length) {
            $keyword = $this->MatchKeyword();
            // $this->debug($keyword);
            if ($keyword === '' && $this->position === $this->length) {
                break;
            }
            // $this->debug($code);
            $identation = '';
            if ($this->putIdentation) {
                $this->putIdentation = false;
                $identation = $this->currentIdentation;
            }

            if (count($breakOn) > 0 && in_array($keyword, $breakOn)) {
                if (
                    !isset($breakConditions[$keyword])
                    || $breakConditions[$keyword]->ParenthesisNormal === $parenthesisNormal
                    || $breakConditions[$keyword]->ParenthesisNormal === null
                ) {
                    $this->lastBreak = $keyword;
                    // $this->debug('Keyword Break: ' . $keyword);
                    $this->position -= strlen($keyword);
                    break;
                }
            }
            $this->lastBreak = null;
            $thisMatched = $this->thisMatched;
            $callFunction = $this->callFunction;
            $this->callFunction = null;
            $skipLastSaving = false;
            // $this->debug('Keyword: ' . $keyword);
            if ($keyword[0] === '$') {
                if ($this->IsPhpVariable($this->lastKeyword)) {
                    $code .= ' ';
                    // $this->debug($this->lastKeyword . ' ' . $keyword);
                }
                if ($keyword == '$this') {
                    $this->thisMatched = true;
                    $nextKeyword = $this->MatchKeyword();
                    $this->collectVariablePath = true;
                    $this->currentVariablePath = 'this';
                    $this->latestVariablePath = 'this';
                    if ($nextKeyword === '->') {
                        $this->putIdentation = $identation !== '';
                        // $this->debug($keyword . $nextKeyword);
                        $this->lastKeyword = '->';
                        $this->currentVariablePath .= '.';
                        $this->latestVariablePath .= '.';
                        continue;
                    }
                    $code .= $identation . 'this';
                    $this->position -= strlen($nextKeyword);
                } else {
                    $this->thisMatched = false;
                    if ($this->collectVariablePath) {
                        if (
                            $this->currentVariablePath !== ''
                            && $this->currentVariablePath[strlen($this->currentVariablePath) - 1] !== '.'
                        ) {
                            $this->StopCollectingVariablePath();
                        } else {
                            $this->currentVariablePath .= $keyword;
                        }
                    }
                }
                $varName = substr($keyword, 1);
                $this->latestVariablePath = $varName;
                $this->buffer = $varName;
                $this->bufferIdentation = $identation;
                if ($this->expressionScope && !$this->collectVariablePath) {
                    $this->collectVariablePath = true;
                    $this->currentVariablePath = $varName;
                }
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
                if ($this->IsPhpVariable($this->lastKeyword)) {
                    $code .= ' ';
                }
                $code .= $this->GetVariable($thisMatched);
                $code .= $identation . $keyword;
                // $this->position++;
                if ($this->collectVariablePath) {
                    $this->currentVariablePath .= $keyword;
                }
            } else {
                if ($keyword !== '=') {
                    $code .= $this->GetVariable($thisMatched);
                    if ($this->newVar) {
                        $this->newVar = false;
                    }
                }
                if ($this->collectVariablePath) {
                    if (ctype_alnum($keyword)) {
                        if (
                            $this->currentVariablePath !== ''
                            && $this->currentVariablePath[strlen($this->currentVariablePath) - 1] !== '.'
                        ) {
                            $this->StopCollectingVariablePath();
                        } else {
                            $this->currentVariablePath .= $keyword;
                        }
                    } else if ($keyword === '->') {
                        $this->currentVariablePath .= '.';
                    } else if ($keyword === '[') {
                        $this->currentVariablePath .= '[key]';
                        $this->skipVariableKey = true;
                        $this->collectVariablePath = false;
                    } else if ($keyword === '(') {
                        $this->currentVariablePath .= '()';
                        $this->StopCollectingVariablePath();
                    } else {
                        $this->StopCollectingVariablePath();
                    }
                }
                switch ($keyword) {
                    case '=': {
                            $code .= $this->GetVariable($thisMatched);
                            $code .= ' = ';
                            break;
                        }
                    case '->': {
                            $code .= '.';
                            $this->latestVariablePath .= '.';
                            break;
                        }
                    case '[]': {
                            if ($this->IsPhpVariable($this->lastKeyword)) {
                                // $this->debug($this->lastKeyword . ' : ' . $keyword);
                                // $this->debug('Latest: ' . $this->latestVariablePath);
                                // TODO: improve array reactivity: nested arrays, indexes,array_pop/push, set by index, etc.
                                $this->pasteArrayReactivity = [$this->latestVariablePath, "'push'"];
                                // $this->debug($this->pasteArrayReactivity);
                                $code .= '.push(';
                                $this->SkipToTheSymbol('=');
                                $code .= $this->ReadCodeBlock(';');
                                $code .= ')';
                            } else {
                                $code .= '[]';
                            }
                            break;
                        }
                    case ')': {
                            $code .= ')';
                            $parenthesisNormal--;
                            break;
                        }
                    case '(': {
                            if (
                                $callFunction !== null
                                && isset(self::$functionConverters[$callFunction])
                            ) {
                                $this->lastKeyword = $keyword;
                                // $this->debug($callFunction);                                
                                $code = self::$functionConverters[$callFunction]::Convert(
                                    $this,
                                    $code,
                                    $identation
                                );
                            } else {
                                // TODO: put ' ' after if, else, switch, etc.
                                $code .= $identation . ($callFunction !== null ? '' : '') . '(';
                            }
                            $parenthesisNormal++;
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
                            $code .=  ($this->lastKeyword === ')' ? ' ' : '') .
                                '{' . PHP_EOL . $this->currentIdentation;
                            // $this->debug('OPEN BLOCK ' . $keyword . $blocksLevel . '<<<====' . $code . '====>>>');
                            $this->newVar = true;
                            break;
                        }
                    case '}': {
                            $blocksLevel--;
                            if ($blocksLevel < 0) {
                                $this->position++;
                                // $this->debug('CLOSE BLOCK ' . $keyword . $blocksLevel . '<<<====' . $code . '====>>>');
                                $this->lastKeyword = $keyword;
                                break 2;
                            }
                            $this->currentIdentation = // $lastIdentation;
                                substr($this->currentIdentation, 0, -strlen($this->identation));
                            $code .= $this->currentIdentation . '}' . PHP_EOL;
                            $this->putIdentation = true;
                            $this->newVar = true;
                            break;
                        }
                    case ';': {
                            $this->position++;
                            $code .= ';' . PHP_EOL;
                            $this->putIdentation = true;
                            $this->newVar = true;
                            if ($this->pasteArrayReactivity) {
                                $code .= $this->currentIdentation .
                                    'notify(' . implode(', ', $this->pasteArrayReactivity) . ');'
                                    . PHP_EOL;
                                $this->pasteArrayReactivity = false;
                            }
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
                            $this->newVar = true;
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
                    case '<<<': {
                            // $this->debug('<<< detected');
                            $code .= $identation . $this->ReadHereDocString() . PHP_EOL;
                            break;
                        }
                    case '[': {
                            $code .= $this->ReadArray(']');
                            if ($this->skipVariableKey) {
                                $this->collectVariablePath = true;
                                $this->skipVariableKey = false;
                            }
                            break;
                        }
                    case 'elseif': {
                            $code .= $identation . 'else if ';
                            break;
                        }
                    case 'array': {
                            $code .= $this->ReadArray(')');
                            $skipLastSaving = true;
                            break;
                        }
                    case 'use': {
                            $this->ProcessUsing();
                            $skipLastSaving = true;
                            break;
                        }
                    case 'namespace': {
                            $this->ProcessNamespace();
                            $skipLastSaving = true;
                            break;
                        }
                    case 'class': {
                            $code .= $identation . $this->ProcessClass();
                            $skipLastSaving = true;
                            break;
                        }
                    case 'for': {
                            $code .= $identation . $this->ReadFor();
                            $skipLastSaving = true;
                            break;
                        }
                    case 'foreach': {
                            $code .= $identation . $this->ReadForEach();
                            $skipLastSaving = true;
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
                                $this->scope[$this->scopeLevel][$propertyName . '_this'] = $keyword;
                            } else {
                                // type
                                $name = $this->MatchKeyword();
                                $propertyName = substr($name, 1);
                                $code .= $identation . ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->scopeLevel][$propertyName] = $keyword;
                                $this->scope[$this->scopeLevel][$propertyName . '_this'] = $keyword;
                            }
                            $symbol = $this->MatchKeyword();
                            $this->lastKeyword = $symbol;
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
                        if (isset($this->processors[$keyword]) || isset($this->spaces[$keyword])) {
                            // $this->debug($this->lastKeyword . ' ' . $keyword);
                            if (
                                $this->IsPhpVariable($this->lastKeyword)
                                && $this->IsPhpVariable($keyword)
                            ) {
                                $code .= ' ';
                            }
                            $before = $identation;
                            $after = '';
                            if (isset($this->spaces[$keyword])) {
                                if ($identation === '') {
                                    $before = $this->spaces[$keyword][0];
                                }
                                $after = $this->spaces[$keyword][1];
                                if ($after !== '') {
                                    $skipLastSaving = true;
                                    $this->lastKeyword = ' ';
                                }
                            }
                            $code .=  $before . $keyword . $after;
                            // $this->position++;
                        } else if (ctype_alnum(str_replace('_', '', $keyword))) {
                            // $this->debug($keyword);
                            if (
                                isset(self::$functionConverters[$keyword])
                                && self::$functionConverters[$keyword]::$directive
                            ) {
                                $code = self::$functionConverters[$keyword]::Convert(
                                    $this,
                                    $code . $keyword,
                                    $identation
                                );
                                $skipLastSaving = true;
                            } else {
                                // $this->debug($keyword . ' after "' . $this->lastKeyword . '"');
                                // if ($this->lastKeyword !== ' ') {
                                $this->callFunction = $keyword;
                                // $this->debug($keyword);
                                //}
                                // $this->debug($this->lastKeyword . ' ' . $keyword);
                                $this->thisMatched = false;
                                if ($thisMatched) {
                                    $this->buffer = $keyword;
                                    $this->bufferIdentation = $identation;
                                    $code .= $this->GetVariable($thisMatched);
                                } else {
                                    if ($this->IsPhpVariable($this->lastKeyword)) {
                                        // $this->debug($this->lastKeyword . ' ' . $keyword);
                                        $code .= ' ';
                                    }
                                    $code .= $identation . $keyword;
                                }
                            }
                        } else {
                            $this->position++;
                            $code .= $identation . "'Undefined keyword `$keyword` at ReadCodeBlock.'";
                            break 2;
                        }
                }
            }
            if (!$skipLastSaving) {
                $this->lastKeyword = $keyword;
            }
        }
        $code .= $this->GetVariable($thisMatched);
        return $code;
    }

    public function IsPhpVariable(string $string): bool
    {
        return ctype_alnum(str_replace('_', '', str_replace('$', '', $string)));
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
            $this->lastKeyword = ';';
            $this->position++;
            $loop .= $separator . $part;
            $separator = '; ';
        }
        $this->lastKeyword = ')';
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
        $this->lastKeyword = ';';
        $var = $this->ReadCodeBlock('=>', ')');
        if ($this->lastBreak === '=>') {
            $this->position += 2;
            $index = $var;
            $this->lastKeyword = ';';
            $var = $this->ReadCodeBlock(')');
        } else {
            $this->position += 1;
        }

        $loop .= "(var $index in $target) {" . PHP_EOL;
        $this->SkipToTheSymbol('{');
        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;
        $loop .= "{$this->currentIdentation}var $var = {$target}[{$index}];";
        $this->lastKeyword = ';';
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
                // $this->debug('Extends: ' . $extends);
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
            $classCode .= PHP_EOL . $this->currentIdentation . "this.__construct.apply(this, arguments);"
                . PHP_EOL;
            $arguments = $this->constructors[$className]['arguments'];
        }

        $classHead .= $arguments . ') ' . '{' . PHP_EOL . $classCode . '};' . PHP_EOL . PHP_EOL;
        $this->lastKeyword = '}';
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

        $this->currentMethod = $functionName;

        $this->scopeLevel++;
        $this->scope[$this->scopeLevel] = [];
        // read function arguments
        $arguments = $this->ReadArguments();
        $functionCode .= $arguments['arguments'] . ') ';

        // read function body
        $this->SkipToTheSymbol('{');
        $this->lastKeyword = '{';
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
        $this->lastKeyword = '}';
        // $this->debug('==========' . $functionCode . '==========');
        if ($constructor) {
            $this->constructors[$this->currentClass] = $arguments;
        }
        $this->currentMethod = null;
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
            $comma = $newLineFormat ? ', ' . PHP_EOL . $valueIdentation : ', ';
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
        $this->lastKeyword = '[';
        while ($this->position < $this->length) {
            $item = $this->ReadCodeBlock(',', '=>', $closing, '(', ';');
            $this->lastKeyword = $this->lastBreak;
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

    private function ReadHereDocString(): string
    {
        $stopWord = '';
        $code = '';
        // get stopword
        while ($this->position < $this->length) {
            if (ctype_space($this->parts[$this->position])) {
                break;
            }
            $stopWord .= $this->parts[$this->position];
            $this->position++;
        }
        // $this->debug($stopWord);
        while ($this->position < $this->length) {
            if (
                $this->parts[$this->position] === "\n"
                || $this->parts[$this->position] === "\r"
            ) {
                // check stopword
                $buffer = '';
                $word = '';
                $break = false;

                while ($this->position < $this->length) {
                    $buffer .= $this->parts[$this->position];
                    if (ctype_alpha($this->parts[$this->position])) {
                        $word .= $this->parts[$this->position];
                    } else if ($word !== '') {
                        if ($word === $stopWord) {
                            $break = true;
                            // $this->debug($word);
                            // $this->debug($buffer);
                            // $this->debug($code);
                        } else {
                            $code .= $buffer;
                        }
                        $this->position++;
                        break;
                    }
                    $this->position++;
                }
                if ($break) {
                    break;
                }
            }
            $code .= $this->parts[$this->position];
            $this->position++;
        }
        return $code;
    }

    private function ReadDoubleQuoteString(): string
    {
        $parts = [];
        $string = '';
        $skipNext = false;

        while ($this->position < $this->length) {
            if ($this->parts[$this->position] !== '"' || $skipNext) {
                if (!$skipNext && $this->parts[$this->position] === '\\') {
                    $skipNext = true;
                } else {
                    $skipNext = false;
                }
                if ($this->parts[$this->position] === '{') {
                    if ($string !== '') {
                        $parts[] = "\"$string\"";
                        $string = '';
                    }
                    $this->position++;
                    $this->lastKeyword = '{';
                    $string .= $this->ReadCodeBlock('}');
                    $parts[] = $string;
                    $string = '';
                    $this->position++;
                    continue;
                }
                if ($this->parts[$this->position] === '$' && $this->parts[$this->position + 1] !== '$') {
                    // variable: "$var" or "$arr[2]"
                    if ($string !== '') {
                        $parts[] = "\"$string\"";
                        $string = '';
                    }
                    $this->position++;
                    while (ctype_alnum($this->parts[$this->position]) || $this->parts[$this->position] === '_') {
                        $string .= $this->parts[$this->position];
                        $this->position++;
                    }
                    if ($this->parts[$this->position] === '[') {
                        $this->position++;
                        $string .= $this->ReadArray(']');
                    }
                    $parts[] = $string;
                    $string = '';
                    continue;
                }
                $string .= $this->parts[$this->position];
            } else {
                $this->position++;
                if ($string !== '') {
                    $parts[] = "\"$string\"";
                    $string = '';
                }
                break;
            }
            $this->position++;
        }
        return implode(' + ', $parts);
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

    public function SkipToTheSymbol(string $symbol): string
    {
        while ($this->position < $this->length) {
            if ($this->parts[$this->position] === $symbol) {
                $this->position++;
                break;
            }
            $this->position++;
        }
        $this->lastKeyword = $symbol;
        return '';
    }

    private function MatchKeyword(): string
    {
        $keyword = '';
        $firstType = false;
        $operatorKey = false;
        $this->latestSpaces = '';
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
                // 
                if ($keyword !== '' && $operatorKey && !in_array(
                    $keyword . $this->parts[$this->position],
                    $this->allowedOperators[$operatorKey]
                ) && ($this->position + 1 >= $this->length || !in_array(
                    $keyword . $this->parts[$this->position] . $this->parts[$this->position + 1],
                    $this->allowedOperators[$operatorKey]
                ))) {
                    // if ($operatorKey[0] === '<') {
                    //     $this->debug($operatorKey . ' = ' . $keyword);
                    //     $this->debug(
                    //         $this->parts[$this->position] .
                    //             $this->parts[$this->position + 1] .
                    //             $this->parts[$this->position + 2] .
                    //             $this->parts[$this->position + 3]
                    //     );
                    // }
                    // $this->position--;
                    // $this->debug('Move BACK' . $this->parts[$this->position - 1] . $this->parts[$this->position]);
                    break;
                }
                $keyword .= $this->parts[$this->position];
            } else { // spaces
                if ($keyword !== '') {
                    break;
                }
                $this->latestSpaces .= $this->parts[$this->position];
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
