<?php

namespace Viewi\JsTranspile;

use Exception;
use ParseError;
use PhpParser\Error;
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
use PhpParser\Node\Expr\Empty_;
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
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\Float_ as ScalarFloat_;
use PhpParser\Node\Scalar\Int_ as ScalarInt_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
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
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RuntimeException;
use Throwable;

class JsTranspiler
{
    private string $phpCode;
    private string $jsCode = '';
    private ?Parser $parser = null;
    private array $requestedIncludes = [];
    /** @var array<string, UseItem>> */
    private array $usingList = [];
    private bool $inlineExpression = false;
    /**
     * Set while emitting the operand of an isset(): every property fetch, array fetch and method
     * call in that chain becomes optional (?.), so a null/undefined/scalar link yields undefined
     * instead of throwing - which is what PHP's isset() promises. Array KEYS are ordinary
     * expressions and are emitted with it off.
     */
    private bool $nullSafeChain = false;
    private ?string $objectRefName = null;
    /** @var array<string, array> by-reference parameters per PHP function, read once */
    private static array $byRefCache = [];
    /** @var array<string, true> variables first seen as by-reference arguments, declared after the statement */
    private array $pendingDeclarations = [];
    /** @var array<string, mixed>|null built-in PHP constants by name, read once */
    private static ?array $builtInConstants = null;
    private int $level = 0;
    private int $membersCount = 0;
    private string $indentationPattern = '    ';
    private array $privateProperties = [];
    private $stmts;
    private ?string $currentClass = null;
    private array $currentTraits = [];
    private bool $hasConstructor = false;
    private ?string $currentExtend = null;
    private bool $currentConstructor = false;
    private ?string $currentMethod = null;
    private ?string $currentNamespace = null;
    private ?string $buffer = null;
    private string $forks = '';
    private array $localVariables = [];
    /** @var array<string,bool> instance method names in the current class - for the method/property collision guard */
    private array $classMethodNames = [];
    /** @var array<string,bool> instance property names in the current class - for the method/property collision guard */
    private array $classPropertyNames = [];
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
    /**
     * 
     * @var Node | string | null
     */
    private $lastNode = null;

    public function __construct(string $content = '')
    {
        $this->phpCode = $content;
        $this->reset();
    }

    private function reset()
    {
        // v2
        $this->jsCode = '';
        $this->pendingDeclarations = [];
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
        $this->lastNode = null;
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

    /**
     * Emits a call to a Viewi helper the transpiler adds on its own (_phpCastString, _php_compare):
     * a bare name, so it works in component code and template expressions alike, recorded as an
     * Internal use - shipped, but not refused by RestrictedFunctions. A direct call to the same name
     * elsewhere replaces the entry and is checked as usual.
     * @param Node[] $args
     */
    private function internalCall(string $name, array $args): void
    {
        $this->usingList[$name] ??= new UseItem([$name], UseItem::Function, true);
        $this->jsCode .= $name . '(';
        foreach ($args as $i => $arg) {
            if ($i > 0) {
                $this->jsCode .= ', ';
            }
            $this->processStmts([$arg]);
        }
        $this->jsCode .= ')';
    }

    /**
     * PHP's . is string concatenation; JS + adds two numbers and prints true, null and floats its own
     * way. Operands that are not already strings go through _phpCastString. The whole chain is
     * parenthesised (a + b + c), so it stays one operand wherever it lands: -($a . $b), ($a . $b)[0].
     */
    private function concat(BinaryOp\Concat $node, bool $parentheses): void
    {
        if ($parentheses) {
            $this->jsCode .= '(';
        }
        foreach ([[$node->left, $node->right], [$node->right, $node->left]] as $i => [$operand, $other]) {
            if ($i === 1) {
                $this->jsCode .= ' + ';
            }
            if ($operand instanceof BinaryOp\Concat && $i === 0) {
                $this->concat($operand, false); // a left-nested chain stays flat: (a + b + c)
            } elseif ($this->isStringNode($operand) || ($operand instanceof ScalarInt_ && $this->isStringNode($other))) {
                $this->processStmts([$operand]);
            } else {
                $this->internalCall('_phpCastString', [$operand]);
            }
        }
        if ($parentheses) {
            $this->jsCode .= ')';
        }
    }

    /**
     * A PHP constant in browser code: true/false/null as they are; a built-in PHP constant
     * (STR_PAD_LEFT, PHP_EOL, M_PI, JSON_PRETTY_PRINT…) as its value, written in at build time,
     * since the browser has no such names. User-defined and unknown constants fail the build in
     * component code; in a template expression a bare name stays as it is (a member: (click)="runNow").
     * @throws ConvertError
     */
    private function constant(Name $name): string
    {
        $parts = $name->getParts();
        $constant = implode('\\', $parts);
        $lower = strtolower($constant);
        if ($lower === 'true' || $lower === 'false' || $lower === 'null') {
            return $lower;
        }
        if (self::$builtInConstants === null) {
            self::$builtInConstants = [];
            foreach (get_defined_constants(true) as $category => $constants) {
                if ($category !== 'user') {
                    self::$builtInConstants += $constants;
                }
            }
        }
        if (count($parts) > 1 || !array_key_exists($constant, self::$builtInConstants)) {
            if ($this->inlineExpression) {
                // a template expression: a bare name is a component member, (click)="runNow"
                return $constant;
            }
            throw new ConvertError("Constant '$constant' is not a built-in PHP constant: the browser has no user-defined or unknown constants. Use a class constant (SomeClass::NAME) instead.");
        }
        $value = self::$builtInConstants[$constant];
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => 'null',
            is_int($value) => (string)$value,
            is_float($value) && is_nan($value) => 'NaN',
            is_float($value) && is_infinite($value) => $value > 0 ? 'Infinity' : '-Infinity',
            default => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
        };
    }

    /**
     * By-reference parameters of a PHP function, from reflection: [index => true], plus
     * 'variadic' => index when a variadic parameter is by-reference (array_multisort).
     * @return array<int|string, true|int>
     */
    private function byRefParameters(string $function): array
    {
        if (!isset(self::$byRefCache[$function])) {
            $byRef = [];
            if (function_exists($function)) {
                foreach ((new \ReflectionFunction($function))->getParameters() as $i => $parameter) {
                    if ($parameter->isPassedByReference()) {
                        if ($parameter->isVariadic()) {
                            $byRef['variadic'] = $i;
                        } else {
                            $byRef[$i] = true;
                        }
                    }
                }
            }
            self::$byRefCache[$function] = $byRef;
        }
        return self::$byRefCache[$function];
    }

    /** Something PHP can pass by reference: a variable, property or array element. */
    private function isAssignable(Node $node): bool
    {
        return ($node instanceof Variable && $node->name !== 'this')
            || $node instanceof PropertyFetch
            || $node instanceof ArrayDimFetch
            || $node instanceof StaticPropertyFetch;
    }

    /**
     * A by-reference argument: (m = _php_by_ref(m)). The port fills or rewrites the array in place,
     * and a variable that held no array gets a fresh one it can fill (preg_match's $matches).
     */
    private function byRefArgument(Node $value): void
    {
        if ($value instanceof Variable && is_string($value->name) && !$this->inlineExpression
            && !isset($this->localVariables[$value->name])) {
            $this->localVariables[$value->name] = true;
            $this->pendingDeclarations[$value->name] = true;
        }
        $this->jsCode .= '(';
        $this->processStmts([$value]);
        $this->jsCode .= ' = ';
        $this->internalCall('_php_by_ref', [$value]);
        $this->jsCode .= ')';
    }

    /** A node whose JS value is already a string, so concatenation needs no cast. */
    private function isStringNode(Node $node): bool
    {
        if ($node instanceof ConstFetch) {
            try {
                return str_starts_with($this->constant($node->name), '"'); // a built-in string constant: PHP_EOL
            } catch (ConvertError $e) {
                return false; // reported where the constant is emitted
            }
        }
        return $node instanceof String_ || $node instanceof InterpolatedString || $node instanceof BinaryOp\Concat;
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
        } catch (ConvertError $convertError) {
            $error = new JsOutput('');
            $error->errorCode = $this->phpCode;
            $error->errorMessage = $convertError->getMessage();
            if ($this->lastNode !== null) {
                $error->errorPosition = $this->lastNode->getStartFilePos();
                $error->errorEndPosition = $this->lastNode->getEndFilePos();
                $error->errorLine = $this->lastNode->getStartLine();
                // print_r($this->lastNode);
            }
            $error->error = $convertError;
            return $error;
        } catch (Error $convertError) {
            $error = new JsOutput('');
            $error->errorCode = $this->phpCode;
            $error->errorMessage = $convertError->getMessage();

            $error->errorPosition = $convertError->getAttributes()['startFilePos'];
            $error->errorEndPosition = $convertError->getAttributes()['endFilePos'];
            $error->errorLine = $convertError->getStartLine();
            // print_r($this->lastNode);

            $error->error = $convertError;
            return $error;
        } catch (Throwable $exc) {
            throw $exc;
            //     // Helpers::debug([$this->phpCode,  $this->jsCode, $this->forks]);
            //     echo 'Parse Error: ' . PHP_EOL, $exc->getMessage() . PHP_EOL;
            //     // Helpers::debug($this->phpCode);

        }
        // tokens
        $tokens = [];
        if (!$this->inlineExpression) {
            $currentToken = '';
            $raw = str_split($this->phpCode);
            $length = count($raw);
            $i = 0;
            while ($i < $length) {
                $char = $raw[$i];
                if (ctype_alnum($char) || $char === '-'  || $char === '_') {
                    $currentToken .= $char;
                } else {
                    if ($currentToken !== '') {
                        $tokens[$currentToken] = 1;
                        $currentToken = '';
                    }
                }

                $i++;
            }

            if ($currentToken !== '') {
                $tokens[$currentToken] = 1;
                $currentToken = '';
            }
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
        return new JsOutput($this->jsCode, $this->exports, $this->usingList, $this->variablePaths, $this->transforms, $tokens);
    }

    /**
     * 
     * @param array<Node\Stmt|string> $stmts 
     * @return void 
     */
    private function processStmts(?array $stmts)
    {
        foreach ($stmts as $node) {
            $this->lastNode = $node;
            // if(!is_string($node)){
            //     print_r($node->getStartFilePos() . " " . $node->getType() . PHP_EOL);
            // }
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
                //print_r($node);
                // Helpers::debug($node);
                // TODO: validation
            } elseif ($node instanceof Interface_) {
                // ignore, javascript does not support interfaces
            } elseif ($node instanceof ClassLike) {
                $exportItem = ExportItem::NewClass($node->name, $this->currentNamespace);
                $extendsCode = '';
                $itsBase = false;
                if ($node instanceof Class_) {
                    if ($node->extends !== null) {
                        $extendParts = $node->extends->getParts();
                        $exportItem->Attributes['extends'] = $extendParts;
                        $extendClass = $exportItem->Attributes['extends'][0];
                        $extendsCode = " extends $extendClass";
                        $itsBase = $extendClass === 'BaseComponent';
                        $this->currentExtend = $extendClass;
                        $this->usingList[$extendClass] = new UseItem($extendParts, UseItem::Class_);
                    }
                    if (count($node->implements) > 0) {
                        $exportItem->Attributes['implements'] = [];
                        foreach ($node->implements as $implement) {
                            $exportItem->Attributes['implements'][$implement->name] = $implement->name;
                        }
                    }
                }
                if ($node instanceof Trait_) {
                    $exportItem->Type = ExportItem::Trait_;
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
                $this->hasConstructor = false;
                $this->classMethodNames = [];
                $this->classPropertyNames = [];
                if ($node->stmts !== null) {
                    $this->currentPath[] = $node->name; // TODO: const
                    $this->processStmts($node->stmts);
                    array_pop($this->currentPath);
                }
                // Guard: PHP keeps method and property namespaces separate, but both transpile to the
                // same JS member (this.x). A method + property sharing a name produces a silently-broken
                // this.x (the instance property shadows the prototype method). Fail loudly instead.
                foreach ($this->classMethodNames as $member => $_) {
                    if (isset($this->classPropertyNames[$member])) {
                        throw new ConvertError(
                            "Component '{$node->name}' declares both a method and a property named '{$member}'. "
                                . "In PHP these are separate, but they transpile to the same JS member (this.{$member}) - "
                                . "the property shadows the method, so calls to {$member}() fail at runtime. Rename one of them."
                        );
                    }
                }
                // "var $this = this;
                // $base(this);"
                $this->membersCount = 0;
                $this->privateProperties = [];

                // if no constructor
                if (!$this->hasConstructor && count($this->currentTraits) > 0) {
                    //$this->jsCode .= "constructor() {";
                    foreach ($this->currentTraits as $trait) {
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) .
                            "/** has trait $trait **/" . PHP_EOL;
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) .
                            "____ = (Object.assign(this, new $trait()));" . PHP_EOL;
                    }
                    
                    //$this->jsCode .= "}";
                }

                $this->level--;
                $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level) . "}" . PHP_EOL;
                foreach ($this->currentTraits as $trait) {
                    // $this->appendTraits[] = $trait;
                    $this->jsCode .= PHP_EOL . str_repeat($this->indentationPattern, $this->level)
                        . "Object.assign({$this->currentClass}.prototype, Object.getOwnPropertyNames($trait.prototype).reduce((x, v) => { x[v] = $trait.prototype[v]; return x; }, {}));"
                        . PHP_EOL;
                }
                $this->currentClass = null;
                $this->currentExtend = null;
            } elseif ($node instanceof TraitUse) {
                foreach ($node->traits as $trait) {
                    $lastPart = $trait->getLast();
                    $this->currentTraits[] = $lastPart;
                    $this->usingList[$lastPart] = new UseItem($trait->getParts(), UseItem::Class_);
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
                    $this->classPropertyNames[$name] = true;
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
                // } elseif ($node instanceof Function_) {
                // }elseif( $node instanceof InlineHTML){


            } elseif ($node instanceof ClassMethod) {
                $name = $node->name->name;
                $itsConstructor = false;
                if ($name === '__construct') {
                    $name = 'constructor';
                    $itsConstructor = true;
                    $this->hasConstructor = true;
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
                                $this->classPropertyNames[$paramName] = true; // promoted ctor param → instance property
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
                    if (!$itsConstructor) {
                        $this->classMethodNames[$name] = true; // instance method (prototype) - for collision guard
                    }
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
                    $hasSuper = false;
                    if ($node->stmts !== null) {
                        foreach ($node->stmts as $stmt) {
                            $childExpression = $stmt;
                            if ($childExpression instanceof Expression) {
                                $childExpression = $childExpression->expr;
                            }
                            if (
                                $childExpression instanceof StaticCall
                                && $childExpression->class->name === 'parent'
                                && $childExpression->name->name === '__construct'
                            ) {
                                $this->processStmts([$stmt]);
                                $stmt->setAttribute('skip', true);
                                $childExpression->name->name = '%skip%';
                                $hasSuper = true;
                            }
                        }
                    }
                    if (!$hasSuper) {
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'super();' . PHP_EOL;
                    }
                }
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'var $this = this;' . PHP_EOL; // TODO: inject only if used
                if ($itsConstructor) {
                    foreach ($this->currentTraits as $trait) {
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) .
                            "/** has trait $trait **/" . PHP_EOL;
                        $this->jsCode .= str_repeat($this->indentationPattern, $this->level) .
                            "Object.assign(this, new $trait());" . PHP_EOL;
                    }
                }
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
                // "v: $f" - every expression part is cast the way PHP casts it (see concat()), and the
                // whole string is parenthesised so it stays one operand: "v: $f"[0]
                $parentheses = count($node->parts) > 1;
                if ($parentheses) {
                    $this->jsCode .= '(';
                }
                $insert = false;
                foreach ($node->parts as $part) {
                    if ($insert) {
                        $this->jsCode .= ' + ';
                    }
                    if ($part instanceof InterpolatedStringPart) {
                        $this->processStmts([$part]);
                    } else {
                        $this->internalCall('_phpCastString', [$part]);
                    }
                    $insert = true;
                }
                if ($parentheses) {
                    $this->jsCode .= ')';
                }
            } elseif ($node instanceof InterpolatedStringPart) {
                $this->jsCode .= json_encode($node->value);
            } elseif ($node instanceof ScalarInt_) {
                $this->jsCode .= $node->getAttribute('rawValue', "{$node->value}");
            } elseif ($node instanceof ScalarFloat_) {
                // Same as an int: JS has one number type, and rawValue keeps the literal as
                // written (1.0 stays 1.0, 1e3 stays 1e3) rather than round-tripping through PHP.
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
                $this->jsCode .= $this->constant($node->name);
            } elseif ($node instanceof PropertyFetch || $node instanceof NullsafePropertyFetch) {
                /**
                 * @var PropertyFetch $node
                 */
                $nullSafe = $node instanceof NullsafePropertyFetch || $this->nullSafeChain;
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
                $nullSafe = $node instanceof NullsafeMethodCall || $this->nullSafeChain;
                // if ($node->name->name === 'await') {
                //     $this->jsCode .= 'await ';
                // }
                $this->processStmts([$node->var]);
                $this->jsCode .= ($nullSafe ? '?.' : '.') . $node->name . '(';

                // }
                if (count($node->args) > 0) {
                    $chain = $this->nullSafeChain;
                    $this->nullSafeChain = false;
                    $comma = '';
                    foreach ($node->args as $argument) {
                        $this->jsCode .= $comma;
                        $this->processStmts([$argument->value]);
                        $comma = ', ';
                    }
                    $this->nullSafeChain = $chain;
                }
                $this->jsCode .= ')';
                // if($node->name->name === 'emitEvent') {
                //      print_r($node);
                // }
            } elseif ($node instanceof StaticCall) {
                // TODO: validate parts
                $class = $node->class->getParts()[0];
                $skipNode = $node->name->name === '%skip%';
                if ($skipNode) {
                    continue;
                }
                $itsConstructor = $node->name->name === '__construct';
                $this->jsCode .= match ($class) {
                    'self' => $this->currentClass,
                    'parent' => 'super',
                    default => $class
                };
                $this->jsCode .= ($itsConstructor ? '' :  '.' . $node->name->name) . '(';
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
                    $this->usingList[$name] = new UseItem($parts, UseItem::Function); // a direct call: never Internal
                } else {
                    $this->processStmts([$node->name]);
                }
                $this->jsCode .= '(';
                $byRef = $node->name instanceof Name ? $this->byRefParameters($node->name->toString()) : [];
                if (count($node->args) > 0) {
                    $comma = '';
                    foreach ($node->args as $index => $argument) {
                        $this->jsCode .= $comma;
                        $referenced = $byRef !== [] && !$argument->unpack
                            && (isset($byRef[$index]) || (isset($byRef['variadic']) && $index >= $byRef['variadic']));
                        if ($referenced && $this->isAssignable($argument->value)) {
                            $this->byRefArgument($argument->value);
                        } else {
                            $this->processStmts([$argument->value]);
                        }
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
                $nameIdParts = $node->class->getParts();
                $className = $nameIdParts[0];
                $this->jsCode .=  $className . '(';
                if ($className !== $this->currentClass) {
                    $this->usingList[$className] = new UseItem($nameIdParts, UseItem::Class_);
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
                // PHP: isset() is false for a missing key/property, a null value, or any link in
                // the chain being null/unset/a scalar - and never throws. JS: an optional chain
                // yields undefined for all of those, and `!= null` rejects null and undefined
                // alike. `k in a` was wrong both ways: true for a key holding null, and a
                // TypeError when `a` is null, undefined, a string or a number.
                // Outer parentheses keep isset($a, $b) a single operand, whatever surrounds it.
                $chain = $this->nullSafeChain;
                $this->jsCode .= '(';
                $comma = '';
                foreach ($node->vars as $var) {
                    $this->jsCode .= $comma . '(';
                    $this->nullSafeChain = true;
                    $this->processStmts([$var]);
                    $this->nullSafeChain = $chain;
                    $this->jsCode .= ' != null)';
                    $comma = ' && ';
                }
                $this->jsCode .= ')';
            } elseif ($node instanceof Empty_) {
                // PHP: empty($x) is !isset($x) || !$x, and never throws. The expression is read as
                // an optional chain (as in isset), so a missing key or property gives undefined, and
                // "falsy" is PHP's: '0', '' and [] are empty, '0.0' and ' ' are not.
                $chain = $this->nullSafeChain;
                $this->nullSafeChain = true;
                $this->jsCode .= '!';
                $this->internalCall('_php_cast_bool', [$node->expr]);
                $this->nullSafeChain = $chain;
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
                    throw new ConvertError("ArrayDimFetch with empty 'dim' should be handled in Assign Expression step.");
                } else {
                    $chain = $this->nullSafeChain;
                    $this->propertyFetchQueue[] = '[]';
                    $this->processStmts([$node->var, $chain ? '?.[' : '[']);
                    array_pop($this->propertyFetchQueue);
                    $queue = $this->propertyFetchQueue;
                    $this->propertyFetchQueue = [];
                    $this->nullSafeChain = false;
                    $this->processStmts([$node->dim, ']']);
                    $this->nullSafeChain = $chain;
                    $this->propertyFetchQueue = $queue;
                }
            } elseif ($node instanceof ClassConstFetch) {
                if ($node->name instanceof Identifier && $node->name->name === 'class') {
                    if ($node->class instanceof Name) {
                        $parts = $node->class->getParts();
                        $this->jsCode .= '"' . array_pop($parts) . '"';
                    } else {
                        $this->processStmts([$node->class]);
                    }
                } else {
                    $classStmt = $node->class;
                    if ($node->class instanceof Name) {
                        $parts = $node->class->getParts();
                        $classStmt = array_pop($parts);
                        if ($classStmt === 'self') {
                            $classStmt = $this->currentClass;
                        }
                    }
                    $nameStmt = $node->class;
                    if ($node->name instanceof Identifier) {
                        $nameStmt = $node->name->name;
                    }
                    $this->processStmts([$classStmt, '.', $nameStmt]);
                }
            } elseif ($node instanceof StaticPropertyFetch) {
                if ($node->name instanceof Identifier && $node->name->name === 'class') {
                    if ($node->class instanceof Name) {
                        $parts = $node->class->getParts();
                        $this->jsCode .= '"' . array_pop($parts) . '"';
                    } else {
                        $this->processStmts([$node->class]);
                    }
                } else {
                    $classStmt = $node->class;
                    if ($node->class instanceof Name) {
                        $parts = $node->class->getParts();
                        $classStmt = array_pop($parts);
                        if ($classStmt === 'self') {
                            $classStmt = $this->currentClass;
                        }
                    }
                    $nameStmt = $node->class;
                    if ($node->name instanceof Identifier) {
                        $nameStmt = $node->name->name;
                    }
                    $this->processStmts([$classStmt, '.', $nameStmt]);
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
                    throw new ConvertError("Node type 'Continue' with number loops to continue is not supported in javascript.");
                }
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level) . 'continue;' . PHP_EOL;
            } elseif ($node instanceof Cast) {
                // PHP's casts, not JS's: (int)'1e3' is 1000 and (int)null is 0 (parseInt gives 1 and
                // NaN), (bool)'0' is false, (string)true is '1'. (object) stays as it is: a PHP array
                // is already an object in the browser.
                $helper = match (true) {
                    $node instanceof Cast\Int_ => '_php_cast_int',
                    $node instanceof Cast\Double => '_php_cast_float',
                    $node instanceof Cast\String_ => '_phpCastString',
                    $node instanceof Cast\Bool_ => '_php_cast_bool',
                    $node instanceof Cast\Array_ => '_php_cast_array',
                    default => null,
                };
                if ($helper === null) {
                    $this->processStmts([$node->expr]);
                } else {
                    $this->internalCall($helper, [$node->expr]);
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
                if ($node->getAttribute('skip')) {
                    continue;
                }
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
                // $s .= $x → s = _phpCastString(s) + _phpCastString(x): JS += would add numbers
                // and print true/null/floats the JS way
                $this->processStmts([$node->var, ' = ']);
                $this->internalCall('_phpCastString', [$node->var]);
                $this->jsCode .= ' + ';
                $this->internalCall('_phpCastString', [$node->expr]);
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
            } elseif ($node instanceof BinaryOp\Concat) {
                $this->concat($node, true);
            } elseif ($node instanceof BinaryOp\Spaceship) {
                // JS has no <=>: PHP 8's comparison rules live in _php_compare
                $this->internalCall('_php_compare', [$node->left, $node->right]);
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
                        // A local variable assignment (LHS is a Variable, never `$this->x`) must always
                        // declare with `var` on first use. Do NOT skip it when the name happens to match a
                        // private method/property: in PHP `$ts` (local) and `$this->ts` (member) are separate
                        // namespaces, and JS keeps them separate too (members are always emitted qualified as
                        // `$this.ts`). Gating on privateProperties here dropped the `var`, leaking the local to
                        // the global scope (e.g. a local `$ts` beside a private `ts()` method).
                        if (!$isThis && !$this->inlineExpression && !isset($this->localVariables[$name])) {
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
                $this->processStmts([$node->var, ' = ']);
                $this->internalCall('_phpCastString', [$node->var]);
                $this->jsCode .= ' + ';
                $this->internalCall('_phpCastString', [$node->expr]);
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
                throw new ConvertError("Node type '{$node->getType()}' is not implemented");
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
                throw new ConvertError("Node type '{$node->getType()}' is not handled in JsTranslator->processStmts");
            }
            if ($node instanceof \PhpParser\Node\Stmt && $this->pendingDeclarations !== []) {
                // a variable first seen as a by-reference argument: var is function-scoped and
                // hoisted, so declaring it after the statement covers its use inside it
                $this->jsCode .= str_repeat($this->indentationPattern, $this->level)
                    . 'var ' . implode(', ', array_keys($this->pendingDeclarations)) . ';' . PHP_EOL;
                $this->pendingDeclarations = [];
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
