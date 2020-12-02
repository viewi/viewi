<?php

$inputs = array_slice($argv, 1);

$file = $inputs[0];

echo "Converting '$file' to php\n";
$converter = new JsToPhpConverter();
$php = $converter->convert(file_get_contents(__DIR__ . "/$file"));
file_put_contents("$file.php", "<?php\n\n$php\n");
class JsToPhpConverter
{
    private string $js;
    private string $outputPhp;
    private int $pos;
    private int $count;
    private string $latestType;
    private int $scopeLevel;
    private string $latestConstructor;
    private string $constructorCode;
    private static array $reserved = [''];
    private array $parts;
    private array $latestTypes;
    private array $latestKeywords;
    private string $latestKeyword;
    private int $debugEchoCount = 0;
    private int $tokensCount = 0;
    private string $currentPart;
    private string $currentSubPart;
    private int $curlyCount;
    private bool $leaveActive;
    const SPACE = 'space';
    const WORD = 'word';
    const OTHER = 'other';

    function reset()
    {
        $this->pos = 0;
        $this->count = strlen($this->js);
        $this->outputPhp = '';
        $this->scopeLevel = 0;
        $this->latestConstructor = '';
        $this->constructorCode = '';
        $this->parts = [];
        $this->latestKeyword = '';
        $this->latestKeywords = ['', '', '', '', '', '', ''];
        $this->latestTypes = [self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE, self::SPACE];
        $this->tokensCount = 0;
        $this->currentPart = '_global';
        $this->currentSubPart = 'code';
        $this->parts[$this->currentPart] = [$this->currentSubPart => []];
        $this->curlyCount = 0;
        $this->leaveActive = false;
    }



    function convert(string $jsText)
    {
        $this->js = $jsText;
        $this->reset();
        while ($this->pos < $this->count) {
            $this->readKeyword();
            $this->regroup();
            $this->matchClassName();
            $this->matchMethodName();
            $this->processTokens();
            $this->leaveParts();
        }
        // print_r($this->parts);
        $this->debugPrint();
        $this->assemble();
        return $this->outputPhp;
    }

    function assemble()
    {
        foreach ($this->parts as $name => $subparts) {
            $this->outputPhp .= "class $name\n{";
            foreach ($subparts as $subName => $code) {
                $this->outputPhp .= "\n    public function $subName";
                $this->outputPhp .=  implode('', $code);
                $this->outputPhp .= "\n";
            }
            $this->outputPhp .= "\n}\n\n";
        }
    }

    function leaveParts()
    {
        if ($this->latestKeyword === '{') {
            $this->leaveActive = true;
            $this->curlyCount++;
            //$this->parts[$this->currentPart][$this->currentSubPart][] = '*{*' . $this->curlyCount;
        }
        if ($this->latestKeyword === '}') {
            $this->curlyCount--;
            //$this->parts[$this->currentPart][$this->currentSubPart][] = '*}*' . $this->curlyCount;
        }
        if ($this->curlyCount < 1 && $this->leaveActive) {
            if (
                $this->currentPart !== '_global'
                && $this->currentSubPart === 'constructor'
            ) {
                // inside class
                // count { and }
                $this->currentPart = '_global';
                $this->currentSubPart = 'code';
                $this->curlyCount = 0;
            }
        }
        if ($this->curlyCount < 1 && $this->leaveActive) {
            if (
                $this->currentPart !== '_global'
                && $this->currentSubPart !== 'constructor'
            ) {
                // inside class method
                // count { and }

                $this->currentSubPart = 'constructor';
                // $this->parts[$this->currentPart][$this->currentSubPart][] = '==END==';
                $this->curlyCount = 1;
            }
        }
    }

    function debugPrint()
    {
        foreach ($this->parts as $name => $subparts) {
            echo "[$name]:\n";
            foreach ($subparts as $subName => $code) {
                echo "[$name][$subName]:\n";
                echo implode('', $code);
                echo "\n";
            }
        }
    }

    function matchMethodName()
    {
        if (
            $this->currentSubPart === 'constructor'
            && $this->latestKeyword === 'function'
            && $this->latestKeywords[1] === '='
            &&
            ($this->latestKeywords[3] === 'var'
                || ($this->latestKeywords[4] === 'this' && $this->latestKeywords[3] === '.'))
        ) {
            if ($this->latestKeywords[4] === 'this') {
                $methodName = $this->latestKeywords[2];
                $this->clearOut(4);
                $this->currentSubPart = $methodName;
            } else {
                $methodName = $this->latestKeywords[2];
                $this->clearOut(4);
                $this->currentSubPart = $methodName;
            }
            $this->parts[$this->currentPart][$this->currentSubPart] = [];
            $this->latestKeyword = '';
            $this->latestType = self::SPACE;
            $this->curlyCount = 0;
            $this->leaveActive = false;
        }
    }

    function matchClassName()
    {
        if (
            $this->currentPart === '_global'
            && $this->latestKeyword === 'function'
            && $this->latestKeywords[3] === 'var'
            && $this->latestKeywords[1] === '='
        ) {
            // echo implode(' | ', $this->latestKeywords);
            // // echo implode('', $this->parts[$this->currentPart][$this->currentSubPart]);

            // echo "\n";

            $className = $this->latestKeywords[2];
            $this->clearOut(4);
            // echo implode('', $this->parts[$this->currentPart][$this->currentSubPart]);
            // echo "class name: $className";
            $this->parts[$className] = [$this->currentSubPart => []];
            // $this->parts[$className]['constructor'] =  '';
            $this->currentPart = $className;
            $this->currentSubPart = 'constructor';
            // echo "\n";
            // echo implode(' | ', $this->latestKeywords);
            // echo "\n";

            $this->latestKeyword = '';
            $this->latestType = self::SPACE;
            $this->curlyCount = 0;
            $this->leaveActive = false;
        }
    }

    function processTokens()
    {
        $token = $this->latestKeyword;
        if ($this->latestType === self::WORD) {
            if ($token === 'var') {
                return;
            }
            if (
                $this->latestKeywords[1] === '.' ||
                in_array($token, ['return', 'null', 'if', 'else', 'function', 'typeof', 'true', 'false', 'for', 'in', 'break'])
            ) {
                $this->parts[$this->currentPart][$this->currentSubPart][] = $token;
            } else {
                $this->parts[$this->currentPart][$this->currentSubPart][] = "\$$token";
            }
        } else {
            if ($token === '.' && $this->latestTypes[1] === self::WORD) {
                $token = '->';
            }
            if ($token === '}' && $this->latestKeywords[1] === '{' && $this->latestKeywords[2] === '=') {
                $this->parts[$this->currentPart][$this->currentSubPart][] = $token;
                $this->clearOut(2);
                $this->parts[$this->currentPart][$this->currentSubPart][] = '[';
                $token = ']';
            }
            $this->parts[$this->currentPart][$this->currentSubPart][] = $token;
        }
    }

    function clearOut(int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            array_shift($this->latestTypes);
            $this->latestTypes[] = '';
            array_shift($this->latestKeywords);
            $this->latestKeywords[] = '';
            $latest = array_pop($this->parts[$this->currentPart][$this->currentSubPart]);
            while (
                count($this->parts[$this->currentPart][$this->currentSubPart]) > 0
                && ctype_space($latest)
            ) {
                $latest = array_pop($this->parts[$this->currentPart][$this->currentSubPart]);
            }
        }
    }

    function regroup()
    {
        if ($this->latestType !== self::SPACE) {
            array_pop($this->latestTypes);
            array_unshift($this->latestTypes, $this->latestType);
            array_pop($this->latestKeywords);
            array_unshift($this->latestKeywords, $this->latestKeyword);
            $this->tokensCount++;
            if ($this->debugEchoCount < 10) {

                $this->debugEchoCount++;
            }
        }
    }

    function readMethod(string $methodName)
    {
        $method = "    public function $methodName(";
        $splitter = '';
        while ($this->pos < $this->count) {
            $keyword = $this->readKeyword();
            if ($keyword === '(' || $keyword === ',' || $this->latestType === self::SPACE) {
                continue;
            }
            if ($keyword === ')' || $keyword === '()') {
                break; // end of constructor arguments
            }
            $method .= "$splitter\$$keyword";
            $splitter = ', ';
        }
        $method .= ")\n    {\n";

        while ($this->pos < $this->count) {
            $keyword = $this->readKeyword();
            if ($keyword === '{') {
                break;
            }
        }
        $this->outputPhp .= $method;
    }

    function readConstructor()
    {
        $constructor = "    public function __construct(";
        $splitter = '';
        while ($this->pos < $this->count) {
            $keyword = $this->readKeyword();
            if ($keyword === '(' || $keyword === ',' || $this->latestType === self::SPACE) {
                continue;
            }
            if ($keyword === ')' || $keyword === '()') {
                break; // end of constructor arguments
            }
            $constructor .= "$splitter\$$keyword";
            $splitter = ', ';
        }
        $constructor .= ")";
        $this->latestConstructor = $constructor;
        while ($this->pos < $this->count) {
            $keyword = $this->readKeyword();
            if ($keyword === '{') {
                break;
            }
        }
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
            // if (in_array($this->js[$this->pos], ['{', '}', '"', "'"])) {
            //     if ($keyword) {
            //         // $this->pos--;
            //     } else {
            //         $keyword = $this->js[$this->pos];
            //         $this->pos++;
            //     }
            //     $this->latestType = $type;
            //     break;
            // }
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
