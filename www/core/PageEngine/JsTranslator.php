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
        '.' => ['.', '.='], '?' => ['?', '??'], ':' => [':'],
    ];
    private array $processors;
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
        // $this->debug($this->processors);
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
            $this->debug($exc->getMessage());
        }
        while ($this->position < $this->length) {
            $this->jsCode .= $this->parts[$this->position];
            $this->position++;
        }

        $this->debug($this->jsCode);
        return $this->jsCode;
    }

    private function ReadCodeBlock(?string $breakOn = null): string
    {
        $code = '';
        while ($this->position < $this->length) {
            $keyword = $this->MatchKeyword();
            if ($breakOn && $breakOn === $keyword) {
                break;
            }
            $this->debug('Keyword: ' . $keyword);

            if ($keyword[0] === '$') {
                if ($keyword === '$this') {
                    $expression = $this->ReadCodeBlock(';');
                    $code .= 'this' . $expression . ';' . PHP_EOL;
                } else {
                    $varName = substr($keyword, 1);
                    $expression = $this->ReadCodeBlock();
                    $code .= $varName . $expression . ';';
                }
            } else if (ctype_digit($keyword)) {
                $code .= $this->MatchKeyword();
                $this->position++;
            } else {

                switch ($keyword) {
                    case '->': {
                            $code .= '.';
                            break;
                        }
                    case '}': {
                            $this->position++;
                            break 2;
                        }
                    case ';': {
                            $this->position++;
                            $code .= ';' . PHP_EOL;
                            break;
                        }
                    case "'": {
                            $code .= $this->ReadSingleQuoteString();
                            break;
                        }
                    case '"': {
                            $code .= $this->ReadDoubleQuoteString();
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
                            $code .= $this->ProcessClass();
                            break;
                        }
                    case 'private':
                    case 'protected':
                    case 'public': {
                            $public = $keyword === 'public';
                            $typeOrName = $this->MatchKeyword();
                            if ($typeOrName === 'function') {
                                $code .= $this->ReadFunction($keyword);
                                break;
                            } else if ($typeOrName[0] === '$') {
                                $propertyName = substr($typeOrName, 1);
                                $code .= ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->currentClass][$propertyName] = $keyword;
                            } else {
                                // type
                                $name = $this->MatchKeyword();
                                $propertyName = substr($name, 1);
                                $code .= ($public ? 'this.' : 'var ') . $propertyName;
                                $this->scope[$this->currentClass][$propertyName] = $keyword;
                            }
                            $symbol = $this->MatchKeyword();
                            if ($symbol === '=') {
                                // match expression
                                $expression = $this->ReadCodeBlock();
                                $code .= " = $expression;" . PHP_EOL;
                            } else if ($symbol !== ';') {
                                throw new Exception("Unexpected symbol `$symbol` detected at ReadCodeBlock.");
                            } else {
                                $code .= ' = null;' . PHP_EOL;
                            }
                            break;
                        }
                    case 'function':
                        $code .= $this->ReadFunction('public');
                        break;
                    default:
                        // $this->debug($code);
                        // throw new Exception("Undefined keyword `$keyword` at ReadCodeBlock.");
                        if (isset($this->processors[$keyword])) {
                            $code .= $keyword;
                        } else if (ctype_alnum($keyword)) {
                            $code .= $keyword;
                        } else {
                            $this->position++;
                            $code .= "'Undefined keyword `$keyword` at ReadCodeBlock.'";
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
        $classHead = "var $className = function(";
        $option = $this->MatchKeyword('{');
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
        $classCode = $this->ReadCodeBlock();
        $this->currentClass = $lastClass;
        $arguments = '';
        $classHead .= $arguments . ')' . PHP_EOL . '{' . PHP_EOL . $classCode . PHP_EOL . '};';
        return $classHead;
    }

    private function ReadClassBody2(): string
    {
        $code = '';
        while ($this->position < $this->length) {
            $keyword = $this->MatchKeyword();

            if ($keyword === '}') {
                return $code; // end of class
            }
            if ($keyword === ';') {
                $this->position++;
                continue;
            }
            switch ($keyword) {
                case 'private':
                case 'protected':
                case 'public': {
                        $public = $keyword === 'public';
                        $typeOrName = $this->MatchKeyword();
                        if ($typeOrName === 'function') {
                            $code .= $this->ReadFunction($keyword);
                            break;
                        } else if ($typeOrName[0] === '$') {
                            $propertyName = substr($typeOrName, 1);
                            $code .= ($public ? 'this.' : 'var ') . $propertyName;
                            $this->scope[$this->currentClass][$propertyName] = $keyword;
                        } else {
                            // type
                            $name = $this->MatchKeyword();
                            $propertyName = substr($name, 1);
                            $code .= ($public ? 'this.' : 'var ') . $propertyName;
                            $this->scope[$this->currentClass][$propertyName] = $keyword;
                        }
                        $symbol = $this->MatchOnlyThese(['=', ';']);
                        if ($symbol === '=') {
                            // match expression
                            $expression = $this->ReadExpression();
                            $code .= " = $expression;" . PHP_EOL;
                        } else if ($symbol !== ';') {
                            throw new Exception("Unexpected symbol `$symbol` detected at ReadClassBody.");
                        } else {
                            $code .= ' = null;' . PHP_EOL;
                        }
                        break;
                    }
                case 'function':
                    $code .= $this->ReadFunction('public');
                    break;
                default:
                    throw new Exception("Undefined keyword `$keyword` at ReadClassBody.");
            }
            // $this->debug($code);
        }
        return $code;
    }

    private function ReadFunction(string $modifier): string
    {
        $private = $modifier === 'private';
        $functionCode = $private ? 'var ' : 'this.';
        $functionName = $this->MatchKeyword();
        $functionCode .= $functionName . ' = function (';

        // read function arguments
        $arguments = $this->ReadArguments();
        $functionCode .= $arguments . ') ';

        // read function body
        $this->SkipToTheSymbol('{');

        $body = $this->ReadCodeBlock();

        $functionCode .= '{' . PHP_EOL . $body . '};' . PHP_EOL;
        return $functionCode;
    }

    // private function ReadCodeBlock(): string
    // {
    //     $block = '';

    //     return $block;
    // }

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
                        $arguments .= ', ' . $this->ReadCodeBlock();
                    } else {
                        $arguments .= $this->ReadCodeBlock();
                    }
                    continue;
                }
            }
            $this->position++;
        }
        return $arguments;
    }



    private function ParseOperator(): string
    {
        // + - * / % ** 
        // = += -= *= /= %=
        // == === != <> !==
        // > < >= <= <=> 
        // ++ --
        // and or xor  && || !
        // . .= 
        // ? : ??
        $operator = '';
        $expected = null;
        while ($this->position < $this->length) {
            if (!ctype_space($this->parts[$this->position])) {
                if (!$operator) {
                    if (isset($this->allowedOperators[$this->parts[$this->position]])) {
                        $expected = $this->allowedOperators[$this->parts[$this->position]];
                    } else {
                    }
                }
                $operator .= $this->parts[$this->position];
            } else {
                if ($operator) {
                    break;
                }
            }
            $this->position++;
        }
        return $operator;
    }

    private function ReadThisExpression(): string
    {
        $expression = '';
        $first = $this->ParseOperator();
        if ($first === '-')
            return $expression;
        return '';
    }

    private function ReadExpression2(): string
    {
        $expression = '';
        while ($this->position < $this->length) {
            if (!ctype_space($this->parts[$this->position])) {
                if ($this->parts[$this->position] === ';') {
                    $this->position++;
                    break;
                }
                if ($this->parts[$this->position] === ',') {
                    break;
                }
                if ($this->parts[$this->position] === ']') {
                    break;
                }
                if (ctype_digit($this->parts[$this->position])) {
                    $expression = $this->ReadNumber();
                    break;
                }
                if (
                    $this->parts[$this->position] === 't'
                    || $this->parts[$this->position] === 'T'
                    || $this->parts[$this->position] === 'f'
                    || $this->parts[$this->position] === 'F'
                ) { // boolean true or false
                    if (
                        $this->position + 4 < $this->length
                        && strtolower($this->parts[$this->position] .
                            $this->parts[$this->position + 1] .
                            $this->parts[$this->position + 2] .
                            $this->parts[$this->position + 3]) === 'true'
                    ) {
                        $this->position += 4;
                        $expression = 'true';
                        break;
                    }

                    if (
                        $this->position + 5 < $this->length
                        && strtolower($this->parts[$this->position] .
                            $this->parts[$this->position + 1] .
                            $this->parts[$this->position + 2] .
                            $this->parts[$this->position + 3] .
                            $this->parts[$this->position + 4]) === 'false'
                    ) {
                        $this->position += 5;
                        $expression = 'false';
                        break;
                    }
                }
                switch ($this->parts[$this->position]) {
                    case "'":
                        $this->position++;
                        $expression = $this->ReadSingleQuoteString();
                        break 2;
                    case '"':
                        $this->position++;
                        $expression = $this->ReadDoubleQuoteString();
                        break 2;
                    case '[':
                        $this->position++;
                        $expression = $this->ReadArray();
                        break 2;
                    default:
                        $expression = '"Parse error"';
                        // throw new Exception("Unexpected symbol `{$this->parts[$this->position]}` detected at ReadExpression.");
                }
            }
            $this->position++;
        }
        return $expression;
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

    private function ReadNumber(): string
    {
        $number = '';
        while ($this->position < $this->length) {
            if (!ctype_digit($this->parts[$this->position])) {
                break;
            }
            $number .= $this->parts[$this->position];
            $this->position++;
        }
        return $number;
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

    private function MatchOnlyThese(array $symbols): string
    {
        while ($this->position < $this->length) {
            if (
                !ctype_space($this->parts[$this->position])
                && in_array($this->parts[$this->position], $symbols)
            ) {
                $this->position++;
                return $this->parts[$this->position - 1];
            }
            $this->position++;
        }
        return '';
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
                if (!$keyword) {
                    $firstType = ctype_alpha($this->parts[$this->position]) ? 'alpha' : 'number';
                }
                if ($keyword && $firstType === 'operator') {
                    break;
                }
                $keyword .= $this->parts[$this->position];
            } else if (!ctype_space($this->parts[$this->position])) {
                if (!$keyword) {
                    $firstType = 'operator';
                    if (isset($this->allowedOperators[$this->parts[$this->position]])) {
                        $operatorKey = $this->parts[$this->position];
                    }
                }
                if ($keyword && $firstType !== 'operator') {
                    break;
                }
                if ($keyword && $operatorKey && !in_array(
                    $keyword . $this->parts[$this->position],
                    $this->allowedOperators[$operatorKey]
                )) {
                    break;
                }
                $keyword .= $this->parts[$this->position];
            } else {
                if ($keyword) {
                    break;
                }
            }
            if (isset($this->haltSymbols[$keyword])) {
                break;
            }
            $this->position++;
        }
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
