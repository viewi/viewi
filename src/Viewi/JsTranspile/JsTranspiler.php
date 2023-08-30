<?php

namespace Viewi\JsTranspile;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RuntimeException;
use Viewi\Helpers;

class JsTranspiler
{
    // v2
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
                $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            }
            $this->stmts = $this->parser->parse(($this->inlineExpression ? '<?php ' . PHP_EOL : '') . $this->phpCode . ($this->inlineExpression ? ';' : ''));
            $this->processStmts($this->stmts);
            // $this->debug([$this->phpCode,  $this->jsCode, $this->stmts]);
        } catch (Exception $exc) {
            Helpers::debug([$this->phpCode,  $this->jsCode, $this->forks]);
            echo 'Parse Error: ', $exc->getMessage();
            Helpers::debug($this->phpCode);
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
                    $this->exports[$this->currentNamespace] = ExportItem::NewNamespace($this->currentNamespace);
                    $this->processStmts($node->stmts);
                    $this->currentNamespace = null;
                    array_pop($this->currentPath);
                }
            } else if ($node instanceof Use_) {
                foreach ($node->uses as $use) {
                    $parts = $use->name->getParts();
                    $last = $use->name->getLast();
                    $this->usingList[$last] = new UseItem($parts, UseItem::Class_);
                }
                // Helpers::debug($node);
                // TODO: validation
            } else if ($node instanceof Class_) {
                $exportItem = ExportItem::NewClass($node->name, $this->currentNamespace);
                $extendsCode = '';
                $itsBase = false;
                if ($node->extends !== null) {
                    $exportItem->Attributes['extends'] = $node->extends->getParts();
                    $extendClass = $exportItem->Attributes['extends'][0];
                    $extendsCode = " extends $extendClass";
                    $itsBase = $extendClass === 'BaseComponent';
                    $this->currentExtend = $extendClass;
                }
                $this->jsCode .= "class {$node->name}{$extendsCode} {";
                $this->level++;
                $this->currentClass = $node->name;
                $this->variablePaths[$this->currentClass] = [];
                $this->exports[$this->currentNamespace]->Children[$this->currentClass] = $exportItem;
                if ($itsBase) {
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                        "_name = '{$node->name}';";
                }
                // $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '$ = makeProxy(this);' . PHP_EOL;

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
                $this->currentClass = null;
                $this->currentExtend = null;
            } else if ($node instanceof Property) {
                $name = $node->props[0]->name->name;
                $isStatic = $node->isStatic();
                if ($isStatic) {
                    $this->fork();
                    $this->level--;
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . $this->currentClass . ".$name = ";
                } else {
                    $publicOrProtected = !$node->isPrivate();
                    if ($publicOrProtected) {
                        $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "$name = ";
                    } else {
                        $this->privateProperties[$name] = true;
                        $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "\$$name = ";
                    }
                    if ($node->isPublic()) {
                        $this->exports[$this->currentNamespace]->Children[$this->currentClass]->Children[$name] = ExportItem::NewProperty($name);
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
            } else if ($node instanceof ClassMethod) {
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
                            if (
                                ($param->flags & Node\Stmt\Class_::MODIFIER_PUBLIC)
                                || ($param->flags & Node\Stmt\Class_::MODIFIER_PROTECTED)
                            ) {
                                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                                    "$paramName = null;";
                            } elseif ($param->flags & Node\Stmt\Class_::MODIFIER_PRIVATE) {
                                $paramName = '$' . $param->var->name;
                                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) .
                                    "$paramName = null;";
                            }
                            if ($param->default !== null) {
                                $promotedParams[$paramName] =
                                    [
                                        $this->indentationPattern . str_repeat($this->indentationPattern, $this->level) .
                                            'this.' . $paramName . " = $argumentName === undefined ? ",
                                        $param->default,
                                        " : $argumentName;" . PHP_EOL
                                    ];
                            } else {
                                $promotedParams[$paramName] =
                                    [
                                        $this->indentationPattern . str_repeat($this->indentationPattern, $this->level) .
                                            'this.' . $paramName . " = $argumentName;" . PHP_EOL
                                    ];
                            }
                        }
                    }
                    if ($this->membersCount > 0) {
                        $this->jsCode .= PHP_EOL;
                    }
                    $publicOrProtected = !$node->isPrivate();
                    if ($publicOrProtected) {
                        $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "$name(";
                    } else {
                        $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "\$$name(";
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
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                    // TODO: declare js properties for promoted params
                    $this->localVariables[$param->var->name] = true;
                    if ($itsConstructor) {
                        if ($param->flags & Node\Stmt\Class_::MODIFIER_PUBLIC) {
                            $this->exports[$this->currentNamespace]->Children[$this->currentClass]->Children[$param->var->name] = ExportItem::NewProperty($param->var->name);
                        } elseif ($param->flags & Node\Stmt\Class_::MODIFIER_PRIVATE) {
                            $this->privateProperties[$param->var->name] = true;
                        }
                    }
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->level++;
                if ($itsConstructor && $this->currentExtend !== null) {
                    $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'super();' . PHP_EOL;
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
            } else if ($node instanceof String_) {
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
                    $this->jsCode .= $node->getAttribute('rawValue', json_encode($node->value));
                }
                // TODO: miltyline string <<<pre
            } else if ($node instanceof Encapsed) {
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
            } else if ($node instanceof EncapsedStringPart) {
                $this->jsCode .= json_encode($node->value);
            } else if ($node instanceof LNumber) {
                $this->jsCode .= $node->getAttribute('rawValue', "{$node->value}");
            } else if ($node instanceof Foreach_) {
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
                $this->localVariables[$key] = true;
                if ($name !== null) {
                    $this->localVariables[$name] = true;
                }
                $this->processStmts($node->stmts);
                $this->level--;
                $this->localVariables = $allScopes;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } else if ($node instanceof Array_) {
                // TODO: auto new line
                $arrayType = 0; // 0 [], 1 {}
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
                            $this->jsCode .= $comma . $rawItem[0];
                        } else {
                            if (isset($rawItem[1])) {
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
            } else if ($node instanceof ConstFetch) {
                // TODO: validate parts
                $this->jsCode .= implode(',', $node->name->getParts());
            } else if ($node instanceof PropertyFetch) {
                $isThis = $node->var instanceof Variable && $node->var->name === 'this';
                if ($isThis && isset($this->privateProperties[$node->name->name])) {
                    $this->jsCode .= $node->name->name;
                } else {
                    $this->propertyFetchQueue[] = $node->name->name;
                    if ($isThis) {
                        $this->propertyFetchQueue[] = 'this';
                    }
                    $this->processStmts([$node->var]);
                    $this->jsCode .= '.' . $node->name->name;
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
                        array_pop($this->propertyFetchQueue);
                    }
                }
                // $this->debug($node);
            } else if ($node instanceof MethodCall) {
                if ($node->var instanceof Variable && $node->var->name === 'this' && isset($this->privateProperties[$node->name->name])) {
                    $this->jsCode .= $node->name->name . '(';
                } else {
                    $this->processStmts([$node->var]);
                    $this->jsCode .= '.' . $node->name . '(';
                }
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                }
                $this->jsCode .= ')';
            } else if ($node instanceof StaticCall) {
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
            } else if ($node instanceof FuncCall) {
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
            } else if ($node instanceof New_) {
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
            } else if ($node instanceof Closure) {
                $this->jsCode .= "function(";
                $comma = '';
                $allscopes = $this->localVariables;
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                    $this->localVariables[$param->var->name] = true;
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->level++;
                if ($node->stmts !== null) {
                    $this->processStmts($node->stmts);
                }
                $this->localVariables = $allscopes;
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "}";
            } else if ($node instanceof ArrowFunction) {
                $this->jsCode .= "function(";
                $comma = '';
                foreach ($node->params as $param) {
                    $this->jsCode .= $comma . $param->var->name;
                    $comma = ', ';
                }
                $this->jsCode .= ") {" . PHP_EOL;
                $this->processStmts([str_repeat($this->indentationPattern, $this->level + 1) . 'return ', $node->expr, ';' . PHP_EOL]);
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . "}";
            } else if ($node instanceof Variable) {
                $isThis = $node->name === 'this';
                if ($this->objectRefName !== null && $node->name !== $this->objectRefName && !isset($this->localVariables[$node->name])) {
                    $this->jsCode .= $this->objectRefName . '.';
                    $this->transforms['$' . $node->name] = $this->objectRefName . '->' . $node->name;
                }
                $this->jsCode .= $isThis ? ($this->currentConstructor ? 'this' : 'this.$') : $node->name;
                // TODO: variable declaration
            } else if ($node instanceof Isset_) {
                $comma = '';
                foreach ($node->vars as $var) {
                    $this->jsCode .= $comma;
                    if ($var instanceof ArrayDimFetch) {
                        $this->processStmts([$var->dim]);
                        $this->jsCode .= ' in ';
                        $this->processStmts([$var->var]);
                    } else {
                        $this->jsCode .= 'isset(';
                        $this->processStmts([$var]);
                        $this->jsCode .= ')';
                    }
                    $comma = ' && ';
                }
            } else if ($node instanceof ArrayDimFetch) {
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
            } else if ($node instanceof Return_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'return';
                if ($node->expr != null) {
                    $this->jsCode .= ' ';
                    $this->processStmts([$node->expr]);
                }
                $this->jsCode .= ';' . PHP_EOL;
            } else if ($node instanceof Break_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'break;' . PHP_EOL;
            } else if ($node instanceof Ternary) {
                $this->processStmts([$node->cond, ' ? ', $node->if ?? $node->cond, ' : ', $node->else]);
            } else if ($node instanceof Expression) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '';
                if ($node->expr instanceof Assign) {
                    if ($node->expr->var instanceof ArrayDimFetch && $node->expr->var->dim === null) {
                        $this->processStmts([$node->expr->var->var]);
                        $this->jsCode .= '.push(';
                        $this->processStmts([$node->expr->expr]);
                        $this->jsCode .= ')';
                    } else {
                        if ($node->expr->var instanceof Variable) {
                            $name = $node->expr->var->name;
                            $isThis = $name === 'this';
                            if (!$isThis && !$this->inlineExpression && !isset($this->localVariables[$name]) && !isset($this->privateProperties[$name])) {
                                $this->jsCode .= 'var ';
                                $this->localVariables[$name] = true;
                            }
                        }
                        $this->processStmts([$node->expr->var]);
                        $this->jsCode .= ' = ';
                        $this->processStmts([$node->expr->expr]);
                        // TODO: if ArrayDimFetch - notify array change for reactivity
                    }
                    // $this->debug($node);
                } else {
                    $this->processStmts([$node->expr]);
                }
                if (!$this->inlineExpression) {
                    $this->jsCode .= ';' . PHP_EOL;
                }
            } else if ($node instanceof Concat) {
                $this->processStmts([$node->var, ' += ', $node->expr]);
            } else if ($node instanceof If_) {
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
            } else if ($node instanceof While_) {
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'while (';
                $this->processStmts([$node->cond]);
                $this->jsCode .= ') {' . PHP_EOL;
                $this->level++;
                $this->processStmts($node->stmts);
                $this->level--;
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . '}' . PHP_EOL;
            } else if ($node instanceof Switch_) {
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
            } else if ($node instanceof BinaryOp) {
                $this->processStmts([$node->left]);
                $this->jsCode .= ' ' . $node->getOperatorSigil() . ' ';
                $this->processStmts([$node->right]);
            } else if ($node instanceof PostInc) {
                $this->processStmts([$node->var]);
                $this->jsCode .= '++';
            } else if ($node instanceof PostDec) {
                $this->processStmts([$node->var]);
                $this->jsCode .= '--';
            } else if ($node instanceof BooleanNot) {
                $this->jsCode .= '!';
                $this->processStmts([$node->expr]);
            } else if ($node instanceof Nop) {
                $ident = str_repeat($this->indentationPattern, $this->level);
                foreach ($node->getComments() as $comment) {
                    $this->jsCode .=  $ident . $comment . PHP_EOL;
                }
            } else if (is_string($node)) {
                $this->jsCode .= $node;
            } else {
                Helpers::debug([PHP_EOL . $this->phpCode,  PHP_EOL . $this->jsCode, $node]);
                throw new RuntimeException("Node type '{$node->getType()}' is not handled in JsTranslator->processStmts");
            }
        }
    }
}
