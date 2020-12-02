<?php

$inputs = array_slice($argv, 1);

$file = $inputs[0];

echo "Converting '$file' to php\n";
$converter = new JSMinifyService();
$minfied = $converter->convert(file_get_contents(__DIR__ . "/$file"));
file_put_contents("$file.min.js", $minfied);

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

    function convert(string $jsText)
    {
        $this->js = $jsText;
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
        foreach ($this->parts as $part) {
            $this->minified .= implode('', $part['code']);
        }
        $this->minified .= print_r($this->parts, true);
        // $this->minified .= print_r($this->map, true);
        return $this->minified;
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
        $this->curlyCount = 0;
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
                        $this->readKeyword();
                        break;
                    }
                    $comment .= $this->js[$this->pos];
                    $this->pos++;
                }
            }
        }
    }

    function matchRegex()
    {
        if ($this->latestKeyword === '/' && $this->latestTypesList[1] !== self::WORD) {
            // it's a regex
            $regex = $this->latestKeyword;

            while ($this->pos < $this->count) {
                if (
                    $this->js[$this->pos] === '/'
                    && $this->js[$this->pos - 1] !== "\\"
                ) {
                    $regex .= $this->js[$this->pos];
                    $this->parts[$this->currentPart]['code'][] = $regex;
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
            if ($this->latestKeywordsList[1] === 'var') {
                // new variable
                $this->compressWord();
            }
        }
    }

    function matchArguments()
    {
        if ($this->expectOpenCurly && $this->latestType !== self::SPACE) {
            $this->expectOpenCurly = false;
        }
        if ($this->latestKeyword === '{') {
            $this->curlyCount++;
        }
        if ($this->latestKeyword === '}') {
            $this->curlyCount--;
            if ($this->curlyCount <= 0) {
                $this->newPart($this->currentCurlyLevel - 1);
            }
        }
        if ($this->latestKeyword === '(' && $this->latestKeywordsList[1] === 'function') {
            // collect arguments
            $this->collectArguments = true;
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
        $this->latestKeyword = $existen;
    }

    function getMappedWord(): ?string
    {
        // foreach ($this->map as $level => $map) {
        $count = count($this->map);
        for ($i = $count - 1; $i >= 0; $i--) {
            $map = $this->map[$i];
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
        return false;
    }

    function getNewName()
    {
        $words = str_split('qazwsxedcrfvtgbyhnujmikolpQAZWSXEDCRFVTGBYHNUJMIKOLP');
        $wordsCount = count($words);
        $index = 0;
        $first = true;
        while ($first || $this->exsists($words[$index])) {
            $first = false;
            $index = ++$this->map[$this->currentCurlyLevel]['index'];
            if (!isset($words[$index])) {
                throw new Exception('There is not enought words to map! Implement combinations!!');
            }
        }
        $this->map[$this->currentCurlyLevel]['map'][$this->latestKeyword] = $words[$index];
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
                    $mapped = $this->getMappedWord();
                    if ($mapped !== null) {
                        $this->latestKeyword = $mapped;
                    }
                }
            }
            $this->reserveWord();
        }
        $this->parts[$this->currentPart]['code'][] = $this->latestKeyword;
    }

    function newPart(int $curlyLevel = 0)
    {
        $this->currentPart++;
        $this->parts[$this->currentPart] = [
            'level' => $curlyLevel,
            'code' => []
        ];
        $this->currentCurlyLevel = $curlyLevel;
        $this->curlyCount = 0;
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
                            ? self::WORD
                            : self::OTHER);
                }
                if ($type === self::OTHER) {
                    $token .= $this->js[$pos];
                    break;
                }
                if ($type === self::WORD && !ctype_alnum($this->js[$pos])) {
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
                        ? self::WORD
                        : self::OTHER);
            }
            if (
                ($type === self::SPACE && !ctype_space($this->js[$this->pos]))
                || ($type === self::WORD && !ctype_alnum($this->js[$this->pos]))
                || ($type === self::OTHER
                    && (ctype_space($this->js[$this->pos]) || ctype_alpha($this->js[$this->pos])))
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
