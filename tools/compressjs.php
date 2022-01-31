<?php

$inputs = array_slice($argv, 1);

$file = $inputs[0];

echo "minifying '$file'\n";
$start = microtime(true);
$converter = new JSMinifyService();
$minified = $converter->convert(file_get_contents(__DIR__ . "/$file"));
file_put_contents("$file.min.js", $minified);
$end = microtime(true) - $start;
echo "$start\n";
echo "$end\n";
class JSMinifyService
{

    const SPACE = 'space';
    const WORD = 'word';
    const OTHER = 'other';

    private string $js;
    private string $minified;
    private int $pos;
    private int $count;
    private array $latestTypesList;
    private array $latestKeywordsList;
    private string $latestKeyword;
    private string $latestType;
    private array $parts;
    private int $currentPart;
    private bool $collectArguments;
    private array $map;
    private int $currentCurlyLevel;
    private bool $expectOpenCurly;
    private int $curlyCount;
    private bool $collectReserved;
    private array $reservedLevelsWords;
    private array $curlyCountStack;

    function convert(string $jsText)
    {
        $this->js = $jsText;
        $this->hoistingIteration();
        $this->compressIteration();
        // $this->minifyIteration();
        foreach ($this->parts as $part) {
            $this->minified .= implode('', $part['code']);
        }
        // $this->minified .= print_r($this->parts, true);
        // $this->minified .= print_r($this->map, true);
        return $this->minified;
    }

    function minifyIteration()
    {
        $code = [];

        foreach ($this->parts as $part) {
            $code = array_merge($code, $part['code']);
        }
        foreach ($code as $index => $token) {
            $codeToInclude = $token;
            if (
                strlen($token) > 1 &&
                (($token[0] === '/' && $token[1] === '/')
                    || $token[0] === '/' && $token[1] === '*')
            ) {
                if ($token[1] !== '*' || strlen($token) < 3 || $token[2] !== '!') {
                    continue;
                }
                $code[$index] .= "\n";
                $codeToInclude .= "\n";
            }

            if (ctype_space($token)) {
                $codeToInclude = '';
                if (ctype_space($code[$index - 1])) {
                    $codeToInclude = '';
                }
                if (
                    (ctype_alnum(str_replace(['$', '_'], 'a', ($code[$index - 1] ?? ''))))
                    && (ctype_alnum(str_replace(['$', '_'], 'a', ($code[$index + 1] ?? ''))))
                ) {
                    $codeToInclude = ' ';
                    if (
                        strpos(($code[$index] ?? ''), "\n") !== false
                        && !in_array(($code[$index + 1] ?? ''), ['var', 'function'])
                        && !in_array(($code[$index - 1] ?? ''), ['if', 'else', 'in', 'return', 'case', 'typeof'])
                    ) {
                        $codeToInclude = ';';
                    }
                }
                if (
                    ($code[$index - 1] ?? '') === '}'
                    && (($code[$index + 1] ?? '') === 'var'
                        || ($code[$index + 1] ?? '') === 'function')
                ) {
                    $codeToInclude = ';';
                }
                if (
                    ($code[$index - 1] ?? '') !== ';'
                    && ($code[$index - 1] ?? '') !== '{'
                    // && strpos(($code[$index] ?? ''), "\n") === false
                    &&
                    in_array(($code[$index + 1] ?? ''), ['var', 'if', 'for'])
                ) {
                    $codeToInclude = ';';
                }

                if (
                    (ctype_alnum(str_replace(['$', '_'], 'a', ($code[$index + 1] ?? ''))))
                    && in_array(substr(($code[$index - 1] ?? ''), -1, 1), [')', "'", '"', ']'])
                    && !in_array(($code[$index + 1] ?? ''), ['if', 'else', 'in', 'return', 'case', 'typeof', 'catch'])
                ) {
                    $codeToInclude = ';';
                    if (($code[$index - 1] ?? '') === ')') {
                        // exclude if else
                        $level = 1;
                        $i = $index - 2;
                        for ($i; $i >= 0; $i--) {
                            if ($code[$i] === ')') {
                                $level++;
                            }
                            if ($code[$i] === '(') {
                                $level--;
                            }
                            if ($level <= 0) {
                                break;
                            }
                        }
                        if (
                            in_array($code[$i - 1], ['if', 'else'])
                            || in_array($code[$i - 2], ['if', 'else'])
                        ) {
                            $codeToInclude = '';
                        }
                    }
                }

                if (
                    (ctype_alnum(str_replace(['$', '_'], 'a', ($code[$index + 1] ?? ''))))
                    && in_array(substr(($code[$index - 1] ?? ''), -1, 1), ['}'])
                    && !in_array(($code[$index + 1] ?? ''), ['if', 'else', 'in', 'return', 'case', 'typeof', 'catch'])
                ) {
                    $codeToInclude = ';';
                }
            }

            if ($token === "'[object Array]'"
                // strpos($token, 'ssss') !== false
            ) {
                //var_dump(ctype_alnum('0'));
                // echo "========\n";
                // echo $code[$index - 3] . "\n";
                // echo $code[$index - 2] . "\n";
                // echo $code[$index - 1] . "\n";
                // echo $token . "\n";
                // echo $code[$index + 1] . "\n";
                // echo $code[$index + 2] . "\n";
                // echo $code[$index + 3] . "\n";
                // echo $code[$index + 4] . "\n";
                // echo $code[$index + 5] . "\n";
                // var_dump(array_slice($code,$index - 10, 20) );
            }
            $this->minified .= $codeToInclude;
        }
    }

    function compressIteration()
    {
        $this->collectReserved = false;
        $this->iteration();
    }

    function hoistingIteration()
    {
        $this->collectReserved = true;
        $this->reservedLevelsWords = [0 => [
            'as' => 'as', 'in' => 'in',
            'abstract' =>  'abstract',
            'arguments' =>  'arguments',
            'await' =>  'await',
            'boolean' =>  'boolean',
            'break' =>  'break',
            'byte' =>  'byte',
            'case' =>  'case',
            'catch' =>  'catch',
            'char' =>  'char',
            'class' =>  'class',
            'const' =>  'const',
            'continue' =>  'continue',
            'debugger' =>  'debugger',
            'default' =>  'default',
            'delete' =>  'delete',
            'do' =>  'do',
            'double' =>  'double',
            'else' =>  'else',
            'enum' =>  'enum',
            'eval' =>  'eval',
            'export' =>  'export',
            'extends' =>  'extends',
            'false' =>  'false',
            'final' =>  'final',
            'finally' =>  'finally',
            'float' =>  'float',
            'for' =>  'for',
            'function' =>  'function',
            'goto' =>  'goto',
            'if' =>  'if',
            'implements' =>  'implements',
            'import' =>  'import',
            'in' =>  'in',
            'instanceof' =>  'instanceof',
            'int' =>  'int',
            'interface' =>  'interface',
            'let' =>  'let',
            'long' =>  'long',
            'native' =>  'native',
            'new' =>  'new',
            'null' =>  'null',
            'package' =>  'package',
            'private' =>  'private',
            'protected' =>  'protected',
            'public' =>  'public',
            'return' =>  'return',
            'short' =>  'short',
            'static' =>  'static',
            'super' =>  'super',
            'switch' =>  'switch',
            'synchronized' =>  'synchronized',
            'this' =>  'this',
            'throw' =>  'throw',
            'throws' =>  'throws',
            'transient' =>  'transient',
            'true' =>  'true',
            'try' =>  'try',
            'typeof' =>  'typeof',
            'var' =>  'var',
            'void' =>  'void',
            'volatile' =>  'volatile',
            'while' =>  'while',
            'with' =>  'with',
            'yield' =>  'yield'
        ]];
        $this->iteration();
        // print_r($this->reservedLevelsWords);
    }

    function iteration()
    {
        $this->reset();
        while ($this->pos < $this->count) {
            $this->readKeyword();
            $this->regroup();
            // //comments -> (empty)
            $this->matchComments();
            // "str" 'str' -> "str" 'str' // strings
            $this->matchString("'");
            $this->matchString('"');
            // /^\/|\/$/g; -> /^\/|\/$/g; //regex
            $this->matchRegex();
            // ::var RouteItem = function (method, url, action, wheres) 
            // ->var RouteItem = function (a, b, c, d)  // arguments
            $this->matchArguments();
            // this.method = method; -> this.method = a; // properties            
            // { var routes = []; -> { var e = []; // curly scopes
            $this->matchVariables();


            // method.toLowerCase() -> a.toLowerCase() //methods

            $this->collectTokens();
        }
    }

    function reset()
    {
        $this->minified = '';
        $this->pos = 0;
        $this->count = strlen($this->js);
        $this->latestKeyword = '';
        $this->latestType = '';
        $this->latestKeywordsList = ['', '', '', '', '', '', ''];
        $this->latestTypesList = [self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE];
        $this->currentPart = -1;
        $this->parts = [];
        $this->collectArguments = false;
        $this->map = [];
        $this->currentCurlyLevel = 0;
        $this->expectOpenCurly = false;
        $this->curlyCountStack = [];
        $this->curlyCount = 1;
        $this->newPart();
    }

    function matchComments()
    {
        if ($this->latestKeyword === '/') {
            $next = $this->js[$this->pos];
            if ($next === '/') {
                // it's comment, read to the end of line
                // echo 'comment ';
                // var_dump($this->js[$this->pos - 1] . $this->js[$this->pos] . $this->js[$this->pos + 1]);
                $comment = $this->latestKeyword;
                while ($this->pos < $this->count) {
                    if (
                        $this->js[$this->pos] === "\n"
                        ||  $this->js[$this->pos] === "\r"
                    ) {
                        $this->parts[$this->currentPart]['code'][] = $comment;
                        $this->cleanUp(1);
                        // $this->readKeyword();
                        $this->latestKeyword = ' ';
                        $this->latestType = self::SPACE;
                        break;
                    }
                    $comment .= $this->js[$this->pos];
                    $this->pos++;
                }
            } else if ($next === '*') {
                // its multyline comment /* blablabla */
                // echo 'comment ';
                // echo "=======\n" .  substr($this->js, $this->pos - 1, 40) . "\n======\n";

                $comment = $this->latestKeyword;
                while ($this->pos < $this->count) {
                    if (
                        $this->js[$this->pos] === "/"
                        && $this->js[$this->pos - 1] === "*"
                    ) {
                        $comment .= $this->js[$this->pos];
                        $this->pos++;
                        $this->parts[$this->currentPart]['code'][] = $comment;
                        // echo $comment . "\n";
                        $this->cleanUp(1);
                        // $this->readKeyword();
                        $this->latestKeyword = ' ';
                        $this->latestType = self::SPACE;
                        // echo "=======\n" .  substr($this->js, $this->pos - 1, 40) . "\n======\n";
                        // echo implode('', array_reverse($this->latestKeywordsList))."\n";

                        break;
                    }
                    $comment .= $this->js[$this->pos];
                    $this->pos++;
                }
            }
        }
    }

    function cleanUp(int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            array_shift($this->latestTypesList);
            array_shift($this->latestKeywordsList);
            $this->latestTypesList[] = self::SPACE;
            $this->latestKeywordsList[] = '';
        }
    }

    function matchRegex()
    {
        if (
            $this->latestKeyword === '/'
            && (($this->latestTypesList[1] !== self::WORD && !ctype_alnum($this->latestKeywordsList[1]))
                || in_array($this->latestKeywordsList[1], ['return']))
        ) {
            // it's a regex
            $regex = $this->latestKeyword;
            // echo "=======\n" .  substr($this->js, $this->pos - 1, 40) . "\n======\n";
            while ($this->pos < $this->count) {
                if (
                    $this->js[$this->pos] === '/'
                    && $this->js[$this->pos - 1] !== "\\"
                ) {
                    $regex .= $this->js[$this->pos];
                    $this->parts[$this->currentPart]['code'][] = $regex;
                    // echo "=======\n" .  $regex . "\n======\n";
                    $this->pos++;
                    $this->readKeyword();
                    if ($this->latestType === self::WORD) {
                        $this->parts[$this->currentPart]['code'][] = $this->latestKeyword;
                        $this->readKeyword();
                    }

                    break;
                }
                $regex .= $this->js[$this->pos];
                $this->pos++;
            }
        }
    }

    function matchString(string $quote)
    {
        if ($this->latestKeyword === $quote) {
            // it's a 'string', read to the end
            $string = $this->latestKeyword;

            while ($this->pos < $this->count) {
                if (
                    $this->js[$this->pos] === $quote
                    && $this->js[$this->pos - 1] !== "\\"
                ) {
                    $string .= $this->js[$this->pos];
                    $this->parts[$this->currentPart]['code'][] = $string;
                    $this->pos++;
                    $this->readKeyword();
                    break;
                }
                $string .= $this->js[$this->pos];
                $this->pos++;
            }
        }
    }

    function matchVariables()
    {
        if ($this->latestType === self::WORD && $this->currentCurlyLevel > 0) {
            if ($this->latestKeywordsList[1] === 'var' || $this->latestKeywordsList[1] === 'function') {
                // new variable or function
                $this->compressWord();
            }
        }
        if ($this->collectReserved && $this->currentCurlyLevel === 0 && $this->latestType === self::WORD) {
            if ($this->latestKeywordsList[1] === 'var' || $this->latestKeywordsList[1] === 'function') {
                // new variable or function
                $this->reservedLevelsWords[0][$this->latestKeyword] = $this->latestKeyword;
            }
        }
    }
    // private int $debugCount2 = 0;
    function matchArguments()
    {
        if ($this->expectOpenCurly && $this->latestType !== self::SPACE) {
            $this->expectOpenCurly = false;
        }
        if ($this->latestKeyword === '{') {
            // if ($this->debugCount2 < 20) {
            //     echo '{++ ' . $this->currentCurlyLevel . ' ' . implode('', array_reverse($this->latestKeywordsList)) . "\n";
            // }
            // $this->debugCount2++;
            $this->curlyCount++;
        }
        if ($this->latestKeyword === '}') {
            // if ($this->debugCount2 < 20) {
            //     echo '}-- ' . $this->currentCurlyLevel . ' ' . implode('', array_reverse($this->latestKeywordsList)) . "\n";
            // }
            $this->curlyCount--;
            if ($this->curlyCount <= 0) {
                // echo 'function END '.$this->currentCurlyLevel.' ' . implode('', array_reverse($this->latestKeywordsList)) . "\n";
                if (count($this->curlyCountStack) == 0) {
                    // echo '-1 ' . $this->currentCurlyLevel . ' ' . implode('', array_reverse($this->latestKeywordsList)) . "\n";
                    // echo "=======\n" .  substr($this->js, $this->pos - 20, 40) . "\n======\n";
                    $code = substr($this->js, $this->pos - 20, 40);
                    throw new Exception("Something is wrong here:\n $code");
                }
                $this->curlyCount = count($this->curlyCountStack) > 0
                    ?  array_pop($this->curlyCountStack)
                    : 0;
                $this->newPart($this->currentCurlyLevel - 1);
            }
        }
        if (
            ($this->latestKeyword === '(' && $this->latestKeywordsList[1] === 'function')
            || ($this->latestKeyword === '('
                && $this->latestTypesList[1] === self::WORD
                && $this->latestKeywordsList[2] === 'function')
        ) {
            // collect arguments
            // echo 'function BEG '.$this->currentCurlyLevel.' ' . implode('', array_reverse($this->latestKeywordsList)) . "\n";
            $this->collectArguments = true;
            $this->curlyCountStack[] = $this->curlyCount;
            $this->curlyCount = 0;
            $this->newPart($this->currentCurlyLevel + 1);
        }
        // if collecting arguments check for ')'
        if ($this->collectArguments) {
            if ($this->latestType === self::WORD) {
                // compress name
                $this->compressWord();
            }
            if ($this->latestKeyword === ')') {
                $this->collectArguments = false;
                $this->expectOpenCurly = true;
            }
        }
    }

    function compressWord()
    {
        $existen = $this->collectArguments ? null : $this->getMappedWord();
        if ($existen === null) {
            $existen = $this->getNewName();
        }
        // $this->latestKeyword = $existen;
    }

    function getMappedWord(): ?string
    {
        // foreach ($this->map as $level => $map) {
        $keys = array_keys($this->map);
        $count = count($keys);
        for ($i = $count - 1; $i >= 0; $i--) {
            $map = $this->map[$keys[$i]];
            if (isset($map['map'][$this->latestKeyword])) {
                return $map['map'][$this->latestKeyword];
            }
        }
        return null;
    }

    function exsists(string $word): ?string
    {
        foreach ($this->map as $level => $map) {
            if (isset($map['map'][$word])) {
                return true;
            }
        }
        for ($i = $this->currentCurlyLevel; $i >= 0; $i--) {
            if (isset($this->reservedLevelsWords[$i][$word])) {
                return true;
            }
        }
        return false;
    }

    function getNewName()
    {
        $words = str_split('qazwsxedcrfvtgbyhnujmikolpQAZWSXEDCRFVTGBYHNUJMIKOLP$_');
        $wordsCount = count($words);
        $index = 0;
        $first = true;
        $nextWord = $words[$index];
        while ($first || $this->exsists($nextWord)) {
            $first = false;
            $index = ++$this->map[$this->currentCurlyLevel]['index'];
            $sufix = '';
            while ($index >= $wordsCount) {
                $appendix = ($index % $wordsCount);
                $index = ($index / $wordsCount) - 1;
                $sufix = $sufix . $words[$appendix];
            }
            $nextWord = $words[$index] . $sufix;
        }
        $this->map[$this->currentCurlyLevel]['map'][$this->latestKeyword] = $nextWord;
        return $this->map[$this->currentCurlyLevel]['map'][$this->latestKeyword];
    }

    function reserveWord()
    {
        if (!isset($this->map[$this->currentCurlyLevel]['map'][$this->latestKeyword])) {
            $this->map[$this->currentCurlyLevel]['map'][$this->latestKeyword] = $this->latestKeyword;
            // echo $this->latestKeyword . ' ';
        }
    }

    function regroup()
    {
        if ($this->latestType !== self::SPACE) {
            array_pop($this->latestTypesList);
            array_unshift($this->latestTypesList, $this->latestType);
            array_pop($this->latestKeywordsList);
            array_unshift($this->latestKeywordsList, $this->latestKeyword);
        }
    }

    function collectTokens()
    {
        if ($this->latestType === self::WORD) {
            $reserve = true;
            if (
                // in_array($this->latestKeywordsList[1], ['=', '{', '(', ',', '[', ';', '!', '<', '>'])
                !in_array($this->latestKeywordsList[1], ['.'])
                || $this->latestTypesList[1] === self::WORD
            ) {
                $valid = true;

                if (in_array($this->latestKeywordsList[1], ['{', ','])) {
                    // print_r($this->latestKeywordsList);
                    $next = $this->getNextToken();
                    // echo $this->latestKeyword . ' ';
                    // var_dump($next);
                    $valid = $next !== ':';
                }
                if ($valid) {
                    if ($this->collectReserved) {
                        if ($this->currentCurlyLevel === 0) {
                            $this->reservedLevelsWords[0][$this->latestKeyword] = $this->latestKeyword;
                        } else {
                            if (!isset($this->reservedLevelsWords[$this->currentCurlyLevel])) {
                                $this->reservedLevelsWords[$this->currentCurlyLevel] = [];
                            }
                            if (!in_array($this->latestKeywordsList[1], ['var', 'function'])) {
                                $this->reservedLevelsWords[$this->currentCurlyLevel][$this->latestKeyword] = $this->latestKeyword;
                            }
                        }
                    }
                    $mapped = $this->getMappedWord();
                    if ($mapped !== null) {
                        $this->latestKeyword = $mapped;
                        $reserve = false;
                    }
                }
            }
            if ($reserve) {
                $this->reserveWord();
            }
        }
        $this->parts[$this->currentPart]['code'][] = $this->latestKeyword;
    }

    function newPart(int $curlyLevel = 0)
    {
        if ($curlyLevel < 0) {
            return;
        }
        $this->currentPart++;
        $this->parts[$this->currentPart] = [
            'level' => $curlyLevel,
            'code' => []
        ];
        $this->currentCurlyLevel = $curlyLevel;
        if ($this->collectReserved && !isset($this->reservedLevelsWords[$this->currentCurlyLevel])) {
            $this->reservedLevelsWords[$this->currentCurlyLevel] = [];
        }
        if (!isset($this->map[$curlyLevel])) {
            $this->map[$curlyLevel] = [
                'map' => [],
                'index' => -1
            ];
            if (isset($this->map[$curlyLevel - 1])) {
                $this->map[$curlyLevel]['index'] = $this->map[$curlyLevel - 1]['index'];
            }
        }
        $keys = array_keys($this->map);
        foreach ($keys as $key) {
            if ($key > $curlyLevel) {
                unset($this->map[$key]);
            }
        }
    }

    function getNextToken()
    {
        $token = '';
        $pos = $this->pos;
        $type = false;
        while ($pos < $this->count) {
            if (!ctype_space($this->js[$pos])) {
                if (!$type) {
                    $type = ctype_space($this->js[$pos])
                        ? self::SPACE
                        : (ctype_alpha($this->js[$pos])
                            || $this->js[$pos] === '$'
                            || $this->js[$pos] === '_'
                            ? self::WORD
                            : self::OTHER);
                }
                if ($type === self::OTHER) {
                    $token .= $this->js[$pos];
                    break;
                }
                if ($type === self::WORD && !(ctype_alnum($this->js[$pos])
                    || $this->js[$pos] === '$'
                    || $this->js[$pos] === '_')) {
                    break;
                }
                $token .= $this->js[$pos];
            } else if ($type) {
                break;
            }
            $pos++;
        }
        return $token;
    }

    function readKeyword(): string
    {
        $keyword = '';
        $type = false;
        while ($this->pos < $this->count) {
            if (!$type) {
                $type = ctype_space($this->js[$this->pos])
                    ? self::SPACE
                    : (ctype_alpha($this->js[$this->pos])
                        || $this->js[$this->pos] === '$'
                        || $this->js[$this->pos] === '_'
                        ? self::WORD
                        : self::OTHER);
            }
            if (
                ($type === self::SPACE && !ctype_space($this->js[$this->pos]))
                || ($type === self::WORD && !(ctype_alnum($this->js[$this->pos])
                    || $this->js[$this->pos] === '$'
                    || $this->js[$this->pos] === '_'))
                || ($type === self::OTHER
                    && (ctype_space($this->js[$this->pos])
                        || ctype_alpha($this->js[$this->pos])
                        || $this->js[$this->pos] === '$'
                        || $this->js[$this->pos] === '_'))
            ) {
                $this->latestType = $type;
                break;
            }
            $keyword .= $this->js[$this->pos];
            $this->pos++;
            if ($type === self::OTHER) {
                $this->latestType = $type;
                break;
            }
        }
        $this->latestKeyword = $keyword;
        return $keyword;
    }
}
