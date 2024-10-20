<?php

namespace Viewi\JsTranspile;

use Exception;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\Concat;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\Int_ as ScalarInt_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Block;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RuntimeException;

class JsTranspiler
{
    private string $phpCode;
    private string $jsCode = '';
    private ?Parser $parser = null;
    private array $requestedIncludes = [];
    /** @var array<string, UseItem>> */
    private array $usingList = [];
    private bool $inlineExpression = false;
    private ?string $objectRefName = null;
    private int $level = 0;
    private int $membersCount = 0;
    private string $indentationPattern = '    ';
    private array $privateProperties = [];
    private $stmts;
    private ?string $currentClass = null;
    private array $currentTraits = [];
    private ?string $currentExtend = null;
    private bool $currentConstructor = false;
    private ?string $currentMethod = null;
    private ?string $currentNamespace = null;
    private ?string $buffer = null;
    private string $forks = '';
    private array $localVariables = [];
    private int $foreachKeyIndex = 0;
    /** @var array<string,array<string,string[]>> */
    private array $variablePaths;
    private array $currentPath = []; // namespace->class->method
    private array $propertyFetchQueue = []; // this.User.Name; this.List[x].name, etc.
    /**
     * 
     * @var array<string, ExportItem>
     */
    private array $exports = []; // tree of [namespace->]class/function->public method/prop
    private array $transforms = [];
    private array $skipNamespaces = [];

    public function __construct(string $content = '')
    {
        $this->phpCode = $content;
        $this->reset();
    }

    private function reset()
    {
        // v2
        $this->jsCode = '';
        $this->level = 0;
        $this->membersCount = 0;
        $this->privateProperties = [];
        $this->localVariables = [];
        $this->currentClass = null;
        $this->currentExtend = null;
        $this->currentConstructor = false;
        $this->currentMethod = null;
        $this->currentNamespace = null;
        $this->buffer = null;
        $this->forks = '';
        $this->foreachKeyIndex = 0;
        $this->inlineExpression = false;
        $this->variablePaths = [];
        $this->currentPath = [];
        $this->propertyFetchQueue = [];
        $this->exports = [];
        $this->usingList = [];
        $this->transforms = [];
    }

    private function fork()
    {
        $this->buffer = $this->jsCode;
        $this->jsCode = '';
    }

    private function unfork(): string
    {
        $ret = $this->jsCode;
        $this->jsCode = $this->buffer;
        $this->buffer = null;
        $this->forks .= $ret;
        return $ret;
    }

    public function setSkipNamespaces(array $toSkip)
    {
        $this->skipNamespaces = $toSkip;
    }

    public function convert(?string $content = null, bool $inlineExpression = false, ?string $objectRefName = null, array $locals = []): JsOutput
    {
        if ($content !== null) {
            $this->phpCode = $content;
            $this->reset();
        }
        $this->inlineExpression = $inlineExpression;
        $this->objectRefName = $objectRefName;
        $this->localVariables = $locals;
        try {
            if ($this->parser == null) {
                $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
            }
            $this->stmts = $this->parser->parse(($this->inlineExpression ? '<?php ' . PHP_EOL : '') . $this->phpCode . ($this->inlineExpression ? ';' : ''));
            $this->processStmts($this->stmts);
            // $this->debug([$this->phpCode,  $this->jsCode, $this->stmts]);
        } catch (Exception $exc) {
            // Helpers::debug([$this->phpCode,  $this->jsCode, $this->forks]);
            echo 'Parse Error: ' . PHP_EOL, $exc->getMessage() . PHP_EOL;
            // Helpers::debug($this->phpCode);

        }
        $this->jsCode .= $this->forks;
        // die();
        // $this->debug([$this->phpCode,  $this->jsCode]);
        // echo "<table border='1' width='100%'><tbody><tr><td><pre>"
        //     . htmlentities($this->phpCode)
        //     . "</pre></td><td><pre>"
        //     . htmlentities($this->jsCode)
        //     . "</pre></td></tr></tbody></table>";
        // $this->debug($this->variablePaths);
        return new JsOutput($this->jsCode, $this->exports, $this->usingList, $this->variablePaths, $this->transforms);
    }

    /**
     * 
     * @param array<Node\Stmt|string> $stmts 
     * @return void 
     */
    private function processStmts(?array $stmts)
    {
        foreach ($stmts as $node) {
            // use if else for intellisense support. switch does not support it in vs code 
            if ($node instanceof Namespace_) {
                // skip, no namespaces in JS
                if ($node->stmts !== null) {
                    $this->currentPath[] = $node->name; // TODO: const
                    $this->currentNamespace = $node->name;
                    $this->exports[$this->currentNamespace] = ExportItem::NewNamespace($this->currentNamespace ?? '');
                    $this->processStmts($node->stmts);
                    $this->currentNamespace = null;
                    array_pop($this->currentPath);
                }
            } elseif ($node instanceof Use_) {
                foreach ($node->uses as $use) {
                    $parts = $use->name->getParts();
                    $last = $use->name->getLast();
                    $this->usingList[$last] = new UseItem($parts, UseItem::Class_);
                }
                // Helpers::debug($node);
                // TODO: validation
            } elseif ($node instanceof Interface_) {
                // ignore, javascript does not support interfaces
            } elseif ($node instanceof ClassLike) {
                $exportItem = ExportItem::NewClass($node->name, $this->currentNamespace);
                $extendsCode = '';
                $itsBase = false;
                if ($node instanceof Class_ && $node->extends !== null) {
                    $extendParts = $node->extends->getParts();
                    $exportItem->Attributes['extends'] = $extendParts;
                    $extendClass = $exportItem->Attributes['extends'][0];
                    $extendsCode = " extends $extendClass";
                    $itsBase = $extendClass === 'BaseComponent';
                    $this->currentExtend = $extendClass;
                    $this->usingList[$extendClass] = new UseItem($extendParts, UseItem::Class_);
                }
                if ($node->attrGroups) {
                    $exportItem->Attributes['attrs'] = [];
                    foreach ($node->attrGroups as $attributeGroup) {
                        foreach ($attributeGroup->attrs as $attribute) {
                            $attributeParts = $attribute->name->getParts();
                            $exportItem->Attributes['attrs'][$attribute->name->getLast()] = $attributeParts;
                        }
                    }
                }
                $this->jsCode .= "class {$node->name}{$extendsCode} {";
                $this->level++;
                $this->currentClass = $node->name;
                $this->currentTraits = [];
                $this->variablePaths[$this->currentClass] = [];
                $this->exports[$this->currentNamespace]->Children[$this->currentClass] = $exportItem;
                if ($itsBase) {
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                        "_name = '{$node->name}';";
                }
                // $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '$ = makeProxy(this);' . PHP_EOL;
                if (isset($exportItem->Attributes['attrs']['Skip']) || isset($exportItem->Attributes['attrs']['CustomJs'])) {
                    return;
                }
                foreach ($this->skipNamespaces as $namespace) {
                    if (str_starts_with($this->currentNamespace, $namespace)) {
                        return;
                    }
                }

                if ($node->stmts !== null) {
                    $this->currentPath[] = $node->name; // TODO: const
                    $this->processStmts($node->stmts);
                    array_pop($this->currentPath);
                }
                // "var $this = this;
                // $base(this);"
                $this->membersCount = 0;
                $this->privateProperties = [];
                $this->level--;
                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "}" . PHP_EOL;
                foreach ($this->currentTraits as $trait) {
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "Object.assign({$this->currentClass}.prototype, $trait.prototype);" . PHP_EOL;
                }
                $this->currentClass = null;
                $this->currentExtend = null;
            } elseif ($node instanceof TraitUse) {
                foreach ($node->traits as $trait) {
                    $this->currentTraits[] = $trait->getLast();
                }
            } elseif ($node instanceof ClassConst) {
                $this->fork();
                $this->level--;
                foreach ($node->consts as $const) {
                    $name = $const->name->name;
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . $this->currentClass . ".$name = ";
                    $this->processStmts([$const->value, ';']);
                }
                $this->unfork();
                $this->level++;
            } elseif ($node instanceof Property) {
                $name = $node->props[0]->name->name;
                $isStatic = $node->isStatic();
                if ($node->type instanceof Name) {
                    $nameIdParts = $node->type->getParts();
                    $nameIdType = $nameIdParts[0];
                    $this->usingList[$nameIdType] = new UseItem($nameIdParts, UseItem::Class_);
                }
                if ($isStatic) {
                    $this->fork();
                    $this->level--;
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . $this->currentClass . ".$name = ";
                } else {
                    $publicOrProtected = !$node->isPrivate();
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "$name = ";
                    if (!$publicOrProtected) {
                        $this->privateProperties[$name] = true;
                    }
                    if ($node->isPublic()) {
                        // Helpers::debug([$name, $node->type]);
                        $type = null;
                        $nullable = false;
                        if ($node->type instanceof Name) {
                            $type = $node->type->getParts()[0];
                        } elseif ($node->type instanceof NullableType) {
                            $nullable = true;
                            if ($node->type->type instanceof Name) {
                                $type = $node->type->type->getParts()[0];
                            } elseif ($node->type->type instanceof Identifier) {
                                $type = $node->type->type->name;
                            }
                        }
                        $this->exports[$this->currentNamespace]->Children[$this->currentClass]->Children[$name] = ExportItem::NewProperty($name, $type, $nullable);
                    }
                }
                if ($node->props[0]->default !== null) {
                    $this->processStmts([$node->props[0]->default]);
                } else {
                    $this->jsCode .= 'null';
                }
                $this->jsCode .= ';';
                if ($isStatic) {
                    $this->unfork();
                    $this->level++;
                } else {
                    $this->membersCount++;
                }
                // TODO: track public/priv:protected
            } elseif ($node instanceof ClassMethod) {
                $name = $node->name->name;
                $itsConstructor = false;
                if ($name === '__construct') {
                    $name = 'constructor';
                    $itsConstructor = true;
                    $this->currentConstructor = true;
                }
                $promotedParams = [];
                $isStatic = $node->isStatic();
                if ($isStatic) {
                    $this->fork();
                    $this->level--;
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . $this->currentClass . ".$name = function(";
                } else {
                    if ($itsConstructor) {
                        foreach ($node->params as $param) {
                            $paramName = $param->var->name;
                            $argumentName = $param->var->name;
                            $promoted = false;
                            if (
                                ($param->flags & Modifiers::PUBLIC)
                                || ($param->flags & Modifiers::PRIVATE)
                            ) {
                                $promoted = true;
                                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                                    "$paramName = null;";
                            } elseif ($param->flags & Modifiers::PRIVATE) {
                                $promoted = true;
                                $paramName = $param->var->name;
                                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                                    "$paramName = null;";
                            } elseif ($param->flags & Modifiers::PUBLIC) {
                                $promoted = true;
                            }
                            if ($promoted) {
                                $promotedParams[$paramName] =
                                    [
                                        $this->indentationPattern . str_repeat($this->indentationPattern, $this->level) .
                                            '$this.' . $paramName . " = $argumentName;" . PHP_EOL
                                    ];
                            }
                        }
                    }
                    if ($this->membersCount > 0) {
                        $this->jsCode .= PHP_EOL;
                    }
                    $publicOrProtected = !$node->isPrivate();
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "$name(";
                    if (!$publicOrProtected) {
                        $this->privateProperties[$name] = true;
                    }
                    if ($node->isPublic()) {
                        $this->exports[$this->currentNamespace]->Children[$this->currentClass]->Children[$name] = ExportItem::NewMethod($name);
                    }
                }
                $this->currentMethod = $name;
                $this->variablePaths[$this->currentClass][$this->currentMethod] = [];
                $allscopes = $this->localVariables;
                $comma = '';
                $stmtsParams = [];
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                    $this->localVariables[$param->var->name] = true;
                    if ($itsConstructor) {
                        if ($param->flags & Modifiers::PUBLIC) {
                            $this->exports[$this->currentNamespace]->Children[$this->currentClass]->Children[$param->var->name] = ExportItem::NewProperty($param->var->name);
                        } elseif ($param->flags & Modifiers::PRIVATE) {
                            $this->privateProperties[$param->var->name] = true;
                        }
                    }
                    if (
                        $param->default !== null
                        // && !isset($promotedParams[$param->var->name]) && !isset($promotedParams['$' . $param->var->name])
                    ) {
                        $stmtsParams[] = str_repeat($this->indentationPattern, $this->level + 1) .
                            "{$param->var->name} = typeof {$param->var->name} !== 'undefined' ? {$param->var->name} : ";
                        $stmtsParams[] = $param->default;
                        $stmtsParams[] = ';' . PHP_EOL;
                    }

                    if ($param->type instanceof Name) {
                        $nameIdParts = $param->type->getParts();
                        $nameIdType = $nameIdParts[0];
                        $this->usingList[$nameIdType] = new UseItem($nameIdParts, UseItem::Class_);
                    }
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->level++;
                if ($itsConstructor && $this->currentExtend !== null) {
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'super();' . PHP_EOL;
                }
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'var $this = this;' . PHP_EOL; // TODO: inject only if used
                if ($stmtsParams) {
                    $this->processStmts($stmtsParams);
                }
                if ($itsConstructor) {
                    foreach ($promotedParams as $paramStmts) {
                        $this->processStmts($paramStmts);
                    }
                }
                if ($node->stmts !== null) {
                    $this->currentPath[] = "$name()";
                    $this->processStmts($node->stmts);
                    array_pop($this->currentPath);
                }
                $this->currentMethod = null;
                $this->currentConstructor = false;
                $this->localVariables = $allscopes;
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "}";
                if ($isStatic) {
                    $this->unfork();
                    $this->level++;
                } else {
                    $this->membersCount++;
                }
            } elseif ($node instanceof String_) {
                $docLabel = $node->getAttribute('docLabel');
                if (in_array($docLabel, ['javascript', "'javascript'"])) {
                    // inject javascript;
                    $parts = explode(PHP_EOL, $node->value);
                    $iden = str_repeat($this->indentationPattern, $this->level);
                    $this->jsCode .= '/** JS injection **/' . PHP_EOL;
                    foreach ($parts as $part) {
                        $this->jsCode .=  $iden . $part . PHP_EOL;
                    }
                    $this->jsCode .=  $iden . '/** END injection **/';
                } else {
                    $this->jsCode .= json_encode($node->value);
                }
                // TODO: multiline string <<<pre
            } elseif ($node instanceof InterpolatedString) {
                $parts = [];
                $insert = false;
                foreach ($node->parts as $part) {
                    if ($insert) {
                        $parts[] = ' + ';
                    }
                    $parts[] = $part;
                    $insert = true;
                }
                $this->processStmts($parts);
            } elseif ($node instanceof InterpolatedStringPart) {
                $this->jsCode .= json_encode($node->value);
            } elseif ($node instanceof ScalarInt_) {
                $this->jsCode .= $node->getAttribute('rawValue', "{$node->value}");
            } elseif ($node instanceof Foreach_) {
                $key = $node->keyVar ?? ('_i' . ($this->foreachKeyIndex++));
                $name = null;
                if ($node->valueVar instanceof Variable) {
                    $name = $node->valueVar->name;
                }
                $this->processStmts([
                    str_repeat($this->indentationPattern, $this->level) . 'for (var ',
                    $key,
                    ' in ',
                    $node->expr,
                    ') {' . PHP_EOL,
                    str_repeat($this->indentationPattern, $this->level + 1),
                    'var ',
                    $name ?? $node->valueVar,
                    ' = ',
                    $node->expr,
                    '[',
                    $key,
                    '];' . PHP_EOL
                ]);
                $this->level++;
                $allScopes = $this->localVariables;
                if (is_string($key)) {
                    $this->localVariables[$key] = true;
                }
                if ($name !== null) {
                    $this->localVariables[$name] = true;
                }
                $this->processStmts($node->stmts);
                $this->level--;
                $this->localVariables = $allScopes;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } elseif ($node instanceof Array_) {
                // TODO: auto new line
                $arrayType = 0; // 0 [], 1 {}
                $comments = $node->getComments();
                if ($comments) {
                    foreach ($comments as $comment) {
                        $commentText = $comment->getText();
                        if (str_contains($commentText, '@jsobject')) {
                            /**
                             * Declaring an empty array ([]) but treat it like associative ins JS ({})
                             * 
                             */
                            // Example: $subscribers = /* @jsobject */ [];
                            $arrayType = 1;
                        }
                    }
                }

                $at = strlen($this->jsCode) - 1;
                if ($node->items !== null) {
                    $rawItems = [];
                    $captured = $this->jsCode;
                    $this->jsCode = '';
                    foreach ($node->items as $item) {
                        $rawItem = [];
                        if ($item->key !== null) {
                            $arrayType = 1;
                            $this->processStmts([$item->key]);
                            $rawItem[] = $this->jsCode;
                            $this->jsCode = '';
                        }
                        if ($item->unpack) {
                            $this->jsCode .= '...';
                            $rawItem[] = true;
                        }
                        $this->processStmts([$item->value]);
                        $rawItem[] = $this->jsCode;
                        $this->jsCode = '';
                        $rawItems[] = $rawItem;
                    }
                    $this->jsCode = $captured;
                    if ($arrayType === 0) {
                        $this->jsCode .= '[';
                    } else {
                        $this->jsCode .= '{';
                    }
                    $comma = '';
                    $index = 0;
                    foreach ($rawItems as $rawItem) {
                        if ($arrayType === 0) {
                            $this->jsCode .= $comma . (isset($rawItem[1]) ?  $rawItem[1] : $rawItem[0]);
                        } else {
                            if ($rawItem[0] === true) {
                                $this->jsCode .= $comma . $rawItem[1];
                            } elseif (isset($rawItem[1])) {
                                $this->jsCode .= $comma . $rawItem[0] . ': ' . $rawItem[1];
                            } else {
                                $this->jsCode .= $comma . "\"$index\"" . ': ' . $rawItem[0];
                            }
                            $index++;
                        }
                        $comma = ', ';
                    }
                    if ($arrayType === 0) {
                        $this->jsCode .= ']';
                    } else {
                        $this->jsCode .= '}';
                    }
                } else {
                    $this->jsCode .= '[]';
                }
            } elseif ($node instanceof ConstFetch) {
                // TODO: validate parts
                $this->jsCode .= implode(',', $node->name->getParts());
            } elseif ($node instanceof PropertyFetch || $node instanceof NullsafePropertyFetch) {
                /**
                 * @var PropertyFetch $node
                 */
                $nullSafe = $node instanceof NullsafePropertyFetch;
                $fetchOperand = $nullSafe ? '?.' : '.';
                $isThis = $node->var instanceof Variable && $node->var->name === 'this';
                if ($isThis && isset($this->privateProperties[$node->name->name])) {
                    $this->jsCode .= '$this' . $fetchOperand . $node->name->name;
                } else {
                    $this->propertyFetchQueue[] = $node->name->name;
                    // if ($isThis) {
                    //     $this->propertyFetchQueue[] = '$this';
                    // }
                    $prevPath = $this->currentPath;
                    $this->processStmts([$node->var]);
                    $fetchBracketS = '';
                    $fetchBracketE = '';
                    if ($node->name instanceof Variable) {
                        $fetchOperand = $nullSafe ? '?.' : '';
                        $fetchBracketS = '[';
                        $fetchBracketE = ']';
                        if ($this->objectRefName !== null && !isset($this->localVariables[$node->name->name])) {
                            $fetchBracketS = '[' . $this->objectRefName . '.';
                            $this->transforms['$' . $node->name->name] = $this->objectRefName . '->' . $node->name->name;
                        }
                        $this->jsCode .= $fetchOperand . $fetchBracketS . $node->name->name . $fetchBracketE;
                    } elseif ($node->name instanceof PropertyFetch) {
                        $fetchOperand = $nullSafe ? '?.' : '';
                        $this->processStmts([$fetchOperand, '[', $node->name, ']']);
                    } else {
                        $this->jsCode .= $fetchOperand . $fetchBracketS . $node->name->name . $fetchBracketE;
                    }
                    if ($isThis) {
                        // $this->debug($this->propertyFetchQueue);
                        $index = count($this->propertyFetchQueue);
                        $path = '';
                        $comma = '';
                        while ($index) {
                            $path .= $comma . $this->propertyFetchQueue[--$index];
                            $comma = '.';
                        }
                        // $this->debug([$path, implode('.', $this->currentPath)]);
                        $this->variablePaths[$this->currentClass][$this->currentMethod][$path] = true;
                        $this->propertyFetchQueue = [];
                    } else {
                        if ($this->inlineExpression) {
                            $this->variablePaths[implode('.', $this->currentPath) . '.' . $node->name->name] = true;
                            // if ($this->phpCode === '$user->name') {
                            //     Helpers::debug([$node, $this->variablePaths, $this->propertyFetchQueue, $this->currentPath]);
                            // }
                            // if (isset($this->localVariables['menuItem'])) {
                            //     print_r($this->variablePaths);
                            // }
                        }
                        array_pop($this->propertyFetchQueue);
                    }
                    $this->currentPath = $prevPath;
                }
                // if ($this->phpCode === '$user->name') {
                //     Helpers::debug([$node, $this->variablePaths, $this->propertyFetchQueue]);
                // }
                // $this->debug($node);
            } elseif ($node instanceof MethodCall || $node instanceof NullsafeMethodCall) {
                // if ($node->var instanceof Variable && $node->var->name === 'this' && isset($this->privateProperties[$node->name->name])) {
                //     $this->jsCode .= $node->name->name . '(';
                // } else {
                $nullSafe = $node instanceof NullsafeMethodCall;
                $this->processStmts([$node->var]);
                $this->jsCode .= ($nullSafe ? '?.' : '.') . $node->name . '(';
                // }
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                }
                $this->jsCode .= ')';
                // if($node->name->name === 'emitEvent') {
                //      print_r($node);
                // }
            } elseif ($node instanceof StaticCall) {
                // TODO: validate parts
                $class = $node->class->getParts()[0];
                $this->jsCode .= $class === 'self' ? $this->currentClass : $class;
                $this->jsCode .= '.' . $node->name . '(';
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                }
                $this->jsCode .= ')';
            } elseif ($node instanceof FuncCall) {
                if ($node->name instanceof Name) {
                    // TODO: validate parts
                    $parts = $node->name->getParts();
                    $name = $parts[0];
                    if ($this->objectRefName !== null && !isset($this->localVariables[$name])) {
                        $this->jsCode .= $this->objectRefName . '.';
                        $this->transforms[$name] = $this->objectRefName . '->' . $name;
                    }
                    $this->jsCode .=  $name;
                    $this->usingList[$name] = new UseItem($parts, UseItem::Function);
                } else {
                    $this->processStmts([$node->name]);
                }
                $this->jsCode .= '(';
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                }
                $this->jsCode .= ')';
                // if ($node->name instanceof Name && $node->name->getParts()[0] === 'gmdate') {
                //     print_r($node);
                // }
            } elseif ($node instanceof New_) {
                // TODO: validate parts
                $this->jsCode .= 'new ';
                $this->jsCode .= $node->class->getParts()[0] . '(';
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                }
                $this->jsCode .= ')';
            } elseif ($node instanceof Closure) {
                $this->jsCode .= "function (";
                $comma = '';
                $allscopes = $this->localVariables;
                $ensureTypes = [];
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                    $this->localVariables[$param->var->name] = true;
                    // if ($param->var->name === 'post') {
                    //     print_r($param);
                    // }
                    // if ($param->type !== null && $param->type instanceof Name) {
                    //     $ensureTypes[$param->var->name] = $param->type->getLast();
                    // } elseif ($param->type !== null && $param->type instanceof NullableType && $param->type->type instanceof Name) {
                    //     $ensureTypes[$param->var->name] = $param->type->type->getLast();
                    // }
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->level++;
                if (count($ensureTypes) > 0) {
                    $this->usingList['ensureType'] = new UseItem(['ensureType'], UseItem::System);
                    foreach ($ensureTypes as $variable => $prototypeClass) {
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "ensureType($prototypeClass, $variable);" . PHP_EOL;
                    }
                }
                if ($node->stmts !== null) {
                    $this->processStmts($node->stmts);
                }
                $this->localVariables = $allscopes;
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "}";
            } elseif ($node instanceof ArrowFunction) {
                $this->jsCode .= "function (";
                $comma = '';
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->processStmts([str_repeat($this->indentationPattern, $this->level + 1) . 'return ', $node->expr, ';' . PHP_EOL]);
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "}";
            } elseif ($node instanceof Variable) {
                $isThis = $node->name === 'this';
                if ($this->objectRefName !== null && $node->name !== $this->objectRefName && !isset($this->localVariables[$node->name])) {
                    $this->jsCode .= $this->objectRefName . '.';
                    $this->transforms['$' . $node->name] = $this->objectRefName . '->' . $node->name;
                }
                $this->jsCode .= $isThis ? ($this->currentConstructor ? '$this' : '$this') : $node->name;
                if ($this->inlineExpression) {
                    $this->variablePaths[$node->name] = true;
                    // print_r($this->variablePaths);
                    $this->currentPath[] = $node->name;
                }
                // TODO: variable declaration
            } elseif ($node instanceof Isset_) {
                $comma = '';
                foreach ($node->vars as $var) {
                    $this->jsCode .= $comma;
                    if ($var instanceof ArrayDimFetch) {
                        $this->processStmts(['(', $var->dim, ' in ', $var->var, ')']);
                    } else {
                        $this->jsCode .= 'isset(';
                        $this->processStmts([$var]);
                        $this->jsCode .= ')';
                    }
                    $comma = ' && ';
                }
            } elseif ($node instanceof Unset_) {
                $comma = '';
                foreach ($node->vars as $var) {
                    $this->jsCode .= $comma;
                    if ($var instanceof ArrayDimFetch) {
                        $this->processStmts([str_repeat($this->indentationPattern, $this->level), '(', 'delete ', $var, ')', ';' . PHP_EOL]);
                    } else {
                        $this->jsCode .= 'unset(';
                        $this->processStmts([$var]);
                        $this->jsCode .= ')';
                    }
                }
            } elseif ($node instanceof ArrayDimFetch) {
                if ($node->dim === null) {
                    throw new RuntimeException("ArrayDimFetch with empty 'dim' should be handled in Assign Expression step.");
                } else {
                    $this->propertyFetchQueue[] = '[]';
                    $this->processStmts([$node->var, '[']);
                    array_pop($this->propertyFetchQueue);
                    $queue = $this->propertyFetchQueue;
                    $this->propertyFetchQueue = [];
                    $this->processStmts([$node->dim, ']']);
                    $this->propertyFetchQueue = $queue;
                }
            } elseif ($node instanceof ClassConstFetch) {
                if ($node->class instanceof Name) {
                    $parts = $node->class->getParts();
                    $this->jsCode .= '"' . array_pop($parts) . '"';
                } else {
                    $this->processStmts([$node->class]);
                }
            } elseif ($node instanceof Return_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'return';
                if ($node->expr != null) {
                    $this->jsCode .= ' ';
                    $this->processStmts([$node->expr]);
                }
                $this->jsCode .= ';' . PHP_EOL;
            } elseif ($node instanceof Continue_) {
                if ($node->num !== null) {
                    throw new RuntimeException("Node type 'Continue' with number loops to continue is not supported in javascript.");
                }
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'continue;' . PHP_EOL;
            } elseif ($node instanceof Cast) {
                // skip
                if ($node instanceof Cast\Int_) {
                    $this->processStmts(['parseInt(', $node->expr, ')']);
                } elseif ($node instanceof Cast\Double) {
                    $this->processStmts(['parseFloat(', $node->expr, ')']);
                } else {
                    $this->processStmts([$node->expr]);
                }
            } elseif ($node instanceof Echo_) {
                $forStmts = [str_repeat($this->indentationPattern, $this->level) . 'console.log('];
                $comma = false;
                foreach ($node->exprs as $expr) {
                    if ($comma) {
                        $forStmts[] = ', ';
                    }
                    $forStmts[] = $expr;
                    $comma = true;
                }
                $forStmts[] = ');' . PHP_EOL;
                $this->processStmts($forStmts);
            } elseif ($node instanceof Break_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'break;' . PHP_EOL;
            } elseif ($node instanceof Ternary) {
                $this->processStmts([$node->cond, ' ? ', $node->if ?? $node->cond, ' : ', $node->else]);
            } elseif ($node instanceof Expression) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '';
                $this->processStmts([$node->expr]);
                if (!$this->inlineExpression || $this->level > 0) {
                    $this->jsCode .= ';' . PHP_EOL;
                }
            } elseif ($node instanceof TryCatch) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'try {' . PHP_EOL;
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
                foreach ($node->catches as $catchStmt) {
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'catch';
                    if ($catchStmt->var !== null) {
                        $this->jsCode .= " ({$catchStmt->var->name})";
                    }
                    $this->jsCode .= ' {' . PHP_EOL;
                    $this->level++;
                    $this->processStmts($catchStmt->stmts);
                    $this->level--;
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
                }
            } elseif ($node instanceof Throw_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'throw ';
                $this->processStmts([$node->expr, ';' . PHP_EOL]);
            } elseif ($node instanceof Concat) {
                $this->processStmts([$node->var, ' += ', $node->expr]);
            } elseif ($node instanceof Block) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '{' . PHP_EOL;
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } elseif ($node instanceof If_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'if (';
                $this->processStmts([$node->cond]);
                $this->jsCode .= ') {' . PHP_EOL;
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
                foreach ($node->elseifs as $elseif) {
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'else if (';
                    $this->processStmts([$elseif->cond]);
                    $this->jsCode .= ') {' . PHP_EOL;
                    $this->level++;
                    $this->processStmts($elseif->stmts);
                    $this->level--;
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
                }
                if ($node->else !== null) {
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'else {' . PHP_EOL;
                    $this->level++;
                    $this->processStmts($node->else->stmts);
                    $this->level--;
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
                }
            } elseif ($node instanceof While_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'while (';
                $this->processStmts([$node->cond]);
                $this->jsCode .= ') {' . PHP_EOL;
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } elseif ($node instanceof For_) {
                $loopStmts = array_merge(
                    [
                        str_repeat($this->indentationPattern, $this->level) . 'for ('
                    ],
                    $node->init,
                    ['; '],
                    $node->cond,
                    ['; '],
                    $node->loop,
                    [') {' . PHP_EOL]
                );
                // print_r($loopStmts);
                $this->processStmts($loopStmts);
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } elseif ($node instanceof Switch_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'switch (';
                $this->processStmts([$node->cond]);
                $this->jsCode .= ') {' . PHP_EOL;
                $this->level++;
                foreach ($node->cases as $case) {
                    $this->processStmts([str_repeat($this->indentationPattern, $this->level) . ($case->cond ? 'case ' : ''), $case->cond ?? 'default', ':' . PHP_EOL]);
                    $this->level++;
                    $this->processStmts($case->stmts);
                    $this->level--;
                }
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } elseif ($node instanceof BinaryOp) {
                $className = get_class($node);
                list($precedence, $associativity) = $this->precedenceMap[$className];
                $wrapParentheses = $node instanceof BinaryOp\Coalesce && $node->right instanceof BinaryOp\BooleanOr;
                $leftParentheses = $this->pPrec($node->left, $precedence, $associativity, -1);
                $leftStmts = $leftParentheses ? ['(', $node->left, ')'] : [$node->left];
                if ($wrapParentheses) {
                    array_unshift($leftStmts, '(');
                }
                $this->processStmts($leftStmts);
                $op = $node->getOperatorSigil();
                $op = $op === '.' ? '+' : $op;
                $this->jsCode .= ' ' . $op . ' ';
                $rightParentheses = $this->pPrec($node->right, $precedence, $associativity, 1);
                $rightStmts = $rightParentheses ? ['(', $node->right, ')'] : [$node->right];
                if ($wrapParentheses) {
                    /**
                     * @var BinaryOp\BooleanOr $rightNode
                     */
                    $rightNode = $node->right;
                    $rightStmts = [$rightNode->left, ')', ' || ', $rightNode->right];
                }
                $this->processStmts($rightStmts);

                // if ($op === '||') {
                //     print_r([$op, $leftParentheses, $rightParentheses, $node]);
                //     // var_dump([!explode('.', '3.4')[1] ?? '5', !explode('.', '3.4'), !(explode('.', '3.4')[1]) ?? '5']);
                //     //Helpers::debug([$op, $leftParentheses, $rightParentheses, $node]);
                // }
            } elseif ($node instanceof PostInc) {
                $this->processStmts([$node->var]);
                $this->jsCode .= '++';
            } elseif ($node instanceof PostDec) {
                $this->processStmts([$node->var]);
                $this->jsCode .= '--';
            } elseif ($node instanceof PreInc) {
                $this->processStmts(['++', $node->var]);
            } elseif ($node instanceof PreDec) {
                $this->processStmts(['--', $node->var]);
            } elseif ($node instanceof Assign) {
                if ($node->var instanceof ArrayDimFetch && $node->var->dim === null) {
                    $this->processStmts([$node->var->var]);
                    $this->jsCode .= '.push(';
                    $this->processStmts([$node->expr]);
                    $this->jsCode .= ')';
                } else {
                    if ($node->var instanceof Variable) {
                        $name = $node->var->name;
                        $isThis = $name === 'this';
                        if (!$isThis && !$this->inlineExpression && !isset($this->localVariables[$name]) && !isset($this->privateProperties[$name])) {
                            $this->jsCode .= 'var ';
                            $this->localVariables[$name] = true;
                        }
                    }
                    $this->processStmts([$node->var]);
                    $this->jsCode .= ' = ';
                    $this->processStmts([$node->expr]);
                    // TODO: if ArrayDimFetch - notify array change for reactivity
                }
                // $this->debug($node);
                // $this->processStmts([$node->var, '=', $node->expr]);
            } elseif ($node instanceof UnaryMinus) {
                $this->processStmts(['-', $node->expr]);
            } elseif ($node instanceof UnaryPlus) {
                $this->processStmts(['+', $node->expr]);
            } elseif ($node instanceof AssignOp\Plus) {
                $this->processStmts([$node->var, '+=', $node->expr]);
            } elseif ($node instanceof AssignOp\Minus) {
                $this->processStmts([$node->var, '-=', $node->expr]);
            } elseif ($node instanceof AssignOp\Mul) {
                $this->processStmts([$node->var, '*=', $node->expr]);
            } elseif ($node instanceof AssignOp\Div) {
                $this->processStmts([$node->var, '/=', $node->expr]);
            } elseif ($node instanceof AssignOp\Concat) {
                $this->processStmts([$node->var, '+=', $node->expr]);
            } elseif ($node instanceof AssignOp\Mod) {
                $this->processStmts([$node->var, '%=', $node->expr]);
            } elseif ($node instanceof AssignOp\BitwiseAnd) {
                $this->processStmts([$node->var, '&=', $node->expr]);
            } elseif ($node instanceof AssignOp\BitwiseOr) {
                $this->processStmts([$node->var, '|=', $node->expr]);
            } elseif ($node instanceof AssignOp\BitwiseXor) {
                $this->processStmts([$node->var, '^=', $node->expr]);
            } elseif ($node instanceof AssignOp\ShiftLeft) {
                $this->processStmts([$node->var, '<<=', $node->expr]);
            } elseif ($node instanceof AssignOp\ShiftRight) {
                $this->processStmts([$node->var, '>>=', $node->expr]);
            } elseif ($node instanceof AssignOp\Pow) {
                throw new RuntimeException("Node type '{$node->getType()}' is not implemented");
            } elseif ($node instanceof AssignOp\Coalesce) {
                $this->processStmts([$node->var, '??=', $node->expr]);
            } elseif ($node instanceof BooleanNot) {
                $this->jsCode .= '!';
                $this->processStmts(['(', $node->expr, ')']);
            } elseif ($node instanceof Nop) {
                $ident = str_repeat($this->indentationPattern, $this->level);
                foreach ($node->getComments() as $comment) {
                    $this->jsCode .=  $ident . $comment . PHP_EOL;
                }
            } elseif (is_string($node)) {
                $this->jsCode .= $node;
            } else {
                // Helpers::debug([PHP_EOL . $this->phpCode,  PHP_EOL . $this->jsCode, $node]);
                // Helpers::debug($node);
                throw new RuntimeException("Node type '{$node->getType()}' is not handled in JsTranslator->processStmts");
            }
        }
    }

    // PHP Parser 

    // https://github.com/nikic/PHP-Parser/blob/a6303e50c90c355c7eeee2c4a8b27fe8dc8fef1d/lib/PhpParser/PrettyPrinterAbstract.php#L27
    protected $precedenceMap = [
        // [precedence, associativity]
        // where for precedence -1 is %left, 0 is %nonassoc and 1 is %right
        BinaryOp\Pow::class            => [0,  1],
        Expr\BitwiseNot::class         => [10,  1],
        Expr\PreInc::class             => [10,  1],
        Expr\PreDec::class             => [10,  1],
        Expr\PostInc::class            => [10, -1],
        Expr\PostDec::class            => [10, -1],
        Expr\UnaryPlus::class          => [10,  1],
        Expr\UnaryMinus::class         => [10,  1],
        Cast\Int_::class               => [10,  1],
        Cast\Double::class             => [10,  1],
        Cast\String_::class            => [10,  1],
        Cast\Array_::class             => [10,  1],
        Cast\Object_::class            => [10,  1],
        Cast\Bool_::class              => [10,  1],
        Cast\Unset_::class             => [10,  1],
        Expr\ErrorSuppress::class      => [10,  1],
        Expr\Instanceof_::class        => [20,  0],
        Expr\BooleanNot::class         => [30,  1],
        BinaryOp\Mul::class            => [40, -1],
        BinaryOp\Div::class            => [40, -1],
        BinaryOp\Mod::class            => [40, -1],
        BinaryOp\Plus::class           => [50, -1],
        BinaryOp\Minus::class          => [50, -1],
        BinaryOp\Concat::class         => [50, -1],
        BinaryOp\ShiftLeft::class      => [60, -1],
        BinaryOp\ShiftRight::class     => [60, -1],
        BinaryOp\Smaller::class        => [70,  0],
        BinaryOp\SmallerOrEqual::class => [70,  0],
        BinaryOp\Greater::class        => [70,  0],
        BinaryOp\GreaterOrEqual::class => [70,  0],
        BinaryOp\Equal::class          => [80,  0],
        BinaryOp\NotEqual::class       => [80,  0],
        BinaryOp\Identical::class      => [80,  0],
        BinaryOp\NotIdentical::class   => [80,  0],
        BinaryOp\Spaceship::class      => [80,  0],
        BinaryOp\BitwiseAnd::class     => [90, -1],
        BinaryOp\BitwiseXor::class     => [100, -1],
        BinaryOp\BitwiseOr::class      => [110, -1],
        BinaryOp\BooleanAnd::class     => [120, -1],
        BinaryOp\BooleanOr::class      => [130, -1],
        BinaryOp\Coalesce::class       => [140,  1],
        Expr\Ternary::class            => [150,  0],
        // parser uses %left for assignments, but they really behave as %right
        Expr\Assign::class             => [160,  1],
        Expr\AssignRef::class          => [160,  1],
        AssignOp\Plus::class           => [160,  1],
        AssignOp\Minus::class          => [160,  1],
        AssignOp\Mul::class            => [160,  1],
        AssignOp\Div::class            => [160,  1],
        AssignOp\Concat::class         => [160,  1],
        AssignOp\Mod::class            => [160,  1],
        AssignOp\BitwiseAnd::class     => [160,  1],
        AssignOp\BitwiseOr::class      => [160,  1],
        AssignOp\BitwiseXor::class     => [160,  1],
        AssignOp\ShiftLeft::class      => [160,  1],
        AssignOp\ShiftRight::class     => [160,  1],
        AssignOp\Pow::class            => [160,  1],
        AssignOp\Coalesce::class       => [160,  1],
        Expr\YieldFrom::class          => [165,  1],
        Expr\Print_::class             => [168,  1],
        BinaryOp\LogicalAnd::class     => [170, -1],
        BinaryOp\LogicalXor::class     => [180, -1],
        BinaryOp\LogicalOr::class      => [190, -1],
        Expr\Include_::class           => [200, -1],
    ];

    // https://github.com/nikic/PHP-Parser/blob/a6303e50c90c355c7eeee2c4a8b27fe8dc8fef1d/lib/PhpParser/PrettyPrinterAbstract.php#L363
    protected function pPrec(Node $node, int $parentPrecedence, int $parentAssociativity, int $childPosition): string
    {
        $class = \get_class($node);
        if (isset($this->precedenceMap[$class])) {
            $childPrecedence = $this->precedenceMap[$class][0];
            if (
                $childPrecedence > $parentPrecedence
                || ($parentPrecedence === $childPrecedence && $parentAssociativity !== $childPosition)
            ) {
                return true;
            }
        }

        return false;
    }
}
