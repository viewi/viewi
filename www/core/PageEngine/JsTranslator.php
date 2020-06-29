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
    private ?string $currentClass = null;
    private array $allowedOperators = [
        '+' => ['+', '+=', '++'], '-' => ['-', '-=', '--', '->'], '*' => ['*', '*=', '**'], '/' => ['/', '/='], '%' => ['%', '%='],
        '=' => ['=', '==', '==='], '!' => ['!', '!=', '!=='], '<' => ['<', '<=', '<=>', '<>'], '>' => ['>', '>='],
        'a' => ['and'], 'o' => ['or'], 'x' => ['xor'], '&' => ['&&'], '|' => ['||'],
        '.' => ['.', '.='], '?' => ['?', '??'], ':' => [':'], ')' => [')'], '{' => ['{'], '}' => ['}'], "'" => ["'"], '"' => ['"'],
        '[' => ['[']
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
        ')' => array(0 => ' ', 1 => ' ',),
        '{' => array(0 => ' ', 1 => ' ',),
        '}' => array(0 => ' ', 1 => ' ',),
        '(' => array(0 => ' ', 1 => ' ',),
        'return' => array(0 => ' ', 1 => ' ',),
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
    public function __construct(string $content)
    {
        $this->phpCode = $content;
        $this->parts = str_split($this->phpCode);
        $this->length = count($this->parts);
        $this->scope = [];
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
    private bool $putIdentation = false;
    private function ReadCodeBlock(...$breakOn): string
    {
        $code = '';
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
                $this->position--;
                break;
            }
            $this->lastBreak = null;
            // $this->debug('Keyword: ' . $keyword);
            if ($keyword[0] === '$') {
                if ($keyword === '$this') {
                    $expression = $this->ReadCodeBlock(...$breakOn + [';']);
                    // $this->debug($expression);
                    $closing = $this->lastBreak === ';' ? '' : '';
                    $code .= $identation . 'this' . $expression . $closing;
                    $this->putIdentation = $closing !== '';
                } else {
                    $varName = substr($keyword, 1);
                    $expression = $this->ReadCodeBlock(...$breakOn + [';']);
                    $closing = $this->lastBreak === ';' ? '' : '';
                    $code .= $identation . $varName . $expression . $closing;
                    $this->putIdentation = $closing !== '';
                }
            } else if (ctype_digit($keyword)) {
                $code .= $identation . $keyword;
                // $this->position++;
            } else {

                switch ($keyword) {
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
                    case '}': {
                            $this->position++;
                            break 2;
                        }
                    case ';': {
                            $this->position++;
                            $code .= ';' . PHP_EOL;
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

                            $code .= $this->ReadArray();
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
                    case 'function':
                        $code .= $identation . $this->ReadFunction('public');
                        break;
                    case 'private':
                    case 'protected':
                    case 'public': {
                            $public = $keyword === 'public';
                            $typeOrName = $this->MatchKeyword();
                            if ($typeOrName === 'function') {
                                $fn = $this->ReadFunction($keyword);
                                $code .= $identation . $fn;
                                break;
                            } else if ($typeOrName[0] === '$') {
                                $propertyName = substr($typeOrName, 1);
                                $code .= $identation . ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->currentClass][$propertyName] = $keyword;
                            } else {
                                // type
                                $name = $this->MatchKeyword();
                                $propertyName = substr($name, 1);
                                $code .= $identation . ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->currentClass][$propertyName] = $keyword;
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
                        } else if (ctype_alnum($keyword)) {
                            $code .= $identation . $keyword;
                        } else {
                            $this->position++;
                            $code .= $identation . "'Undefined keyword `$keyword` at ReadCodeBlock.'";
                            break 2;
                        }
                }
            }
        }
        return $code;
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
        $this->scope[$className] = [];
        $lastClass = $this->currentClass;
        $this->currentClass = $className;

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;
        $this->putIdentation = true;

        $classCode = $this->ReadCodeBlock();
        $this->currentClass = $lastClass;
        $arguments = '';
        $classHead .= $arguments . ') ' . '{' . PHP_EOL . $classCode . '};' . PHP_EOL . PHP_EOL;

        $this->currentIdentation = $lastIdentation;

        // $this->debug('==========' . $classHead . '==========');
        return $classHead;
    }

    private function ReadFunction(string $modifier): string
    {
        $private = $modifier === 'private';
        $functionCode = PHP_EOL . $this->currentIdentation . ($private ? 'var ' : 'this.');
        $functionName = $this->MatchKeyword();
        $functionCode .= $functionName . ' = function (';

        // read function arguments
        $arguments = $this->ReadArguments();
        $functionCode .= $arguments . ') ';

        // read function body
        $this->SkipToTheSymbol('{');

        $lastIdentation = $this->currentIdentation;
        $this->currentIdentation .= $this->identation;
        $this->putIdentation = true;

        $body = $this->ReadCodeBlock();

        $this->currentIdentation = $lastIdentation;

        $functionCode .= '{' . PHP_EOL . $body . $this->currentIdentation . '};' . PHP_EOL;
        // $this->debug('==========' . $functionCode . '==========');
        return $functionCode;
    }

    private function ReadArguments(): string
    {
        $arguments = '';
        $this->SkipToTheSymbol('(');
        while ($this->position < $this->length) {
            if (!ctype_space($this->parts[$this->position])) {
                if ($this->parts[$this->position] === ')') { // close
                    $this->position++;
                    break;
                } else if ($this->parts[$this->position] === ',') { // next item
                    // $this->position++;
                } else {
                    if ($arguments) {
                        $arguments .= ', ' . $this->ReadCodeBlock(',', ')');
                    } else {
                        $arguments .= $this->ReadCodeBlock(',', ')');
                    }
                    continue;
                }
            }
            $this->position++;
        }
        return $arguments;
    }

    private function ReadArray(): string
    {
        $elements = '';

        while ($this->position < $this->length) {
            if (!ctype_space($this->parts[$this->position])) {
                if ($this->parts[$this->position] === ']') { // array closed
                    $this->position++;
                    break;
                } else if ($this->parts[$this->position] === ',') { // next item
                    // $this->position++;
                } else {
                    if ($elements) {
                        $elements .= ', ' . $this->ReadExpression();
                    } else {
                        $elements .= $this->ReadExpression();
                    }
                    // $this->debug($elements . $this->parts[$this->position]);
                    continue;
                }
            }
            $this->position++;
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
        return '';
    }

    private function ProcessUsing(): string
    {
        $this->SkipToTheSymbol(';');
        return '';
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
