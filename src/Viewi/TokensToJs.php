<?php
class TokensToJS
{
    function compileToJs(ReflectionClass $reflectionClass): void
    {
        $className = $reflectionClass->getShortName();
        $tokens = token_get_all(file_get_contents($reflectionClass->getFileName()), TOKEN_PARSE);
        $unresolved = [];
        $php = false;
        $skipWhiteSpace = false;
        $skipUntilNextBlock = false;
        $catchClassName = false;
        $catchExtends = false;
        $catchTypeOrVar = false;
        $currentScope = 0;
        $publicVarOrFunc = false;
        $closeCodeBlock = false;
        $catchFunction = false;
        $privateOrProtected = false;
        $blockLevel = 0;
        $accessObject = false;
        $varOpen = false;
        $varValueOpen = false;
        $assignOperator = false;
        $logicClosed = true;
        $classNameExpected = false;
        $thisAccessor = false;
        $functionDefinition = false;
        $scopes = [0 => ['name' => 'global', 'code' => '', 'block' => 0, 'vars' => []]];
        foreach ($tokens as &$token) {
            if (is_array($token)) {
                $token['t'] = token_name($token[0]);
                if ($varOpen && $token[0] !== T_WHITESPACE) {
                    $varOpen = false;
                }
                if (
                    $thisAccessor
                    && $token[0] !== T_WHITESPACE
                    && $token[0] !== T_OBJECT_OPERATOR
                    && !$accessObject
                ) {
                    $thisAccessor = false;
                }
                switch ($token[0]) {
                    case T_OPEN_TAG:
                        $php = true;
                        break;
                    case T_WHITESPACE:
                        if (!$skipWhiteSpace) {
                            $scopes[$currentScope]['code'] .= $token[1];
                        }
                        break;
                    case T_NAMESPACE:
                    case T_USE:
                        $skipUntilNextBlock = true;
                        $skipWhiteSpace = true;
                        break;
                    case T_CLASS:
                        $catchClassName = true;
                        $skipWhiteSpace = true;
                        break;
                    case T_NS_SEPARATOR:
                        break;
                    case T_ARRAY:
                        break;
                    case T_STRING:
                        if ($classNameExpected) {
                            $classNameExpected = false;
                            $scopes[$currentScope]['code'] .= $token[1];
                            break;
                        }
                        if ($catchClassName) {
                            $catchClassName = false;
                            $currentScope++;
                            $scopes[$currentScope] = ['name' => $token[1], 'code' => '', 'block' => 0, 'vars' => []];
                            // $jscode .= "function {$token[1]}";
                        }
                        if ($catchExtends) {
                            $catchExtends = false;
                            $scopes[$currentScope]['extends'] = $token[1];
                            // $jscode .= "function {$token[1]}";
                        }
                        if ($catchTypeOrVar) {
                            $catchTypeOrVar = false;
                            // js doesn't have type system, skipping
                        }
                        if ($catchFunction) {
                            $catchFunction = false;
                            if ($currentScope > 0) { // inside class
                                if (!$privateOrProtected) { // public method
                                    $scopes[$currentScope]['code'] .= "this.{$token[1]} = function";
                                } else {
                                    $scopes[$currentScope]['code'] .= "var {$token[1]} = function";
                                }
                            } else {
                                $scopes[$currentScope]['code'] .= "function {$token[1]}";
                            }
                            $publicVarOrFunc = false;
                            $privateOrProtected = false;
                        }
                        if ($accessObject) {
                            $accessObject = false;
                            if ($thisAccessor) {
                                if (
                                    isset($scopes[$currentScope]['vars']['$' . $token[1]])
                                    && $scopes[$currentScope]['vars']['$' . $token[1]]
                                ) {
                                    // private or protected property
                                    $scopes[$currentScope]['code'] .= "{$token[1]}";
                                } else {
                                    $scopes[$currentScope]['code'] .= "this.{$token[1]}";
                                }
                            } else {
                                $scopes[$currentScope]['code'] .= ".{$token[1]}";
                            }
                        }
                        if ($token[1] === 'true' || $token[1] === 'false') {
                            $varValueOpen = false;
                            $scopes[$currentScope]['code'] .= $token[1];
                        }
                        break;
                    case T_INC:
                        $scopes[$currentScope]['code'] .= "++";
                        break;
                    case T_NEW:
                        $scopes[$currentScope]['code'] .= 'new';
                        $classNameExpected = true;
                        break;
                    case T_FOR:
                        $scopes[$currentScope]['code'] .= 'for';
                        break;
                    case T_VARIABLE:
                        $catchTypeOrVar = false;
                        if ($token[1] === '$this') {
                            $thisAccessor = true;
                            break;
                        }
                        $varCode = $publicVarOrFunc
                            && !$privateOrProtected
                            && $currentScope > 0
                            ? 'this.' : 'var ';
                        if ($scopes[$currentScope]['block'] > 1) {
                            if ($logicClosed && $token[1] !== '$this') {
                                $varCode = 'var ';
                            } else {
                                $varCode = '';
                            }
                        } else {
                            $scopes[$currentScope]['vars'][$token[1]] = $privateOrProtected;
                        }
                        if ($functionDefinition) {
                            $varCode = '';
                        }
                        // if ($token[1] === '$this') {
                        //     $varCode = '';
                        // }
                        // if ($assignOperator) {
                        //     $varCode = '';
                        // }                        
                        // if($thisCalled){
                        //     $thisCalled = false;
                        //     $varCode = '';
                        // }
                        $jsVarName = substr($token[1], 1);
                        $scopes[$currentScope]['code'] .= "$varCode{$jsVarName}";
                        $publicVarOrFunc = false;
                        $privateOrProtected = false;
                        $varOpen = !$assignOperator;
                        $varValueOpen = true;
                        $skipWhiteSpace = false;
                        break;
                    case T_FUNCTION:
                        $catchFunction = true;
                        $functionDefinition = true;
                        $skipWhiteSpace = true;
                        break;
                    case T_EXTENDS:
                        $catchExtends = true;
                        $skipWhiteSpace = true;
                        break;
                    case T_PRIVATE:
                    case T_PROTECTED:
                        $privateOrProtected = true;
                    case T_PUBLIC:
                        if ($token[0] === T_PUBLIC) {
                            $publicVarOrFunc = true;
                        }
                        $skipWhiteSpace = true;
                        $catchTypeOrVar = true;
                        break;
                    case T_CONSTANT_ENCAPSED_STRING:
                    case T_LNUMBER:
                    case T_DNUMBER:
                        $scopes[$currentScope]['code'] .= "{$token[1]}";
                        $varValueOpen = false;
                        break;
                    case T_OBJECT_OPERATOR:
                        $accessObject = true;
                        break;
                    default:
                        $unresolved[$token[0]] = $token['t'];
                }
                if ($token[0] !== T_WHITESPACE) {
                    $assignOperator = false;
                    $logicClosed = false;
                }
            } else {
                switch ($token) {
                    case ';':
                        if ($varOpen) {
                            $varOpen = false;
                            $scopes[$currentScope]['code'] .= ' = null';
                            $varValueOpen = false;
                        }
                        if (!$skipUntilNextBlock) {
                            $scopes[$currentScope]['code'] .= ';';
                            $skipUntilNextBlock = false;
                        }
                        $skipUntilNextBlock = false;
                        $skipWhiteSpace = false;
                        $logicClosed = true;
                        break;
                    case '{':
                        $skipUntilNextBlock = false;
                        $skipWhiteSpace = false;
                        $scopes[$currentScope]['block']++;
                        $scopes[$currentScope]['code'] .= $token;
                        break;
                    case '}':
                        $scopes[$currentScope]['code'] .= $token;
                        $scopes[$currentScope]['block']--;
                        if ($scopes[$currentScope]['block'] === 0) {
                            $scopes[$currentScope - 1]['code'] .=
                                "var {$scopes[$currentScope]['name']} = function()" .
                                $scopes[$currentScope]['code'];
                            unset($scopes[$currentScope]);
                            $currentScope--;
                        }
                        break;
                    case '=':
                        $scopes[$currentScope]['code'] .= '=';
                        $skipWhiteSpace = false;
                        $varOpen = false;
                        $assignOperator = true;
                        break;
                    case '(':
                    case ')':
                        $logicClosed = true;
                        $scopes[$currentScope]['code'] .= $token;
                        $skipWhiteSpace = false;
                        if ($token === ')') {
                            $functionDefinition = false;
                        }
                        break;
                    case '[':
                        $scopes[$currentScope]['code'] .= '[';
                        $skipWhiteSpace = false;
                        $varOpen = false;
                        $varValueOpen = true;
                        break;
                    case ']':
                        $scopes[$currentScope]['code'] .= $token;
                        $varOpen = false;
                        $varValueOpen = false;
                        break;
                    case ',':
                        $scopes[$currentScope]['code'] .= ',';
                        $varValueOpen = true;
                        break;
                    default:
                        $unresolved[$token] = $token;
                        break;
                }
                $thisAccessor = false;
                if ($token !== '=') {
                    $assignOperator = false;
                }
                if ($token !== ';' && $token !== '(' && $token !== ')') {
                    $logicClosed = false;
                }
            }
        }
    }
}
