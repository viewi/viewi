<?php

namespace Viewi\Services;

class MinifyService
{
    private string $css = '';
    private string $mini = '';
    private int $count;
    private int $i = 0;
    private string $previousToken;
    private bool $previousSafe;
    private bool $previousWhiteSpace;
    private array $safeTokensAfter;
    private array $safeTokensBefore;

    function __construct()
    {
        $this->safeTokensAfter = array_flip(str_split('{},;:)'));
        $this->safeTokensBefore = array_flip(str_split('{},;:('));
    }

    public function minifyJavaScript(string $jsText): string
    {
        return $jsText;
    }

    public function minifyCss(string $cssText): string
    {
        $this->css = $cssText;
        $this->count = strlen($this->css);
        $this->mini = '';
        $this->i = 0;
        $this->previousToken = ' ';
        $this->previousSafe = true;
        $this->previousWhiteSpace = true;
        // print_r($this->safeTokensBefore);
        while ($this->i < $this->count) {
            $keyword = $this->css[$this->i];
            switch ($keyword) {
                case '/': {
                        if ($this->i + 1 < $this->count && $this->css[$this->i + 1] === '*') {
                            // comment begin
                            $this->readComment();
                            break;
                        }
                        $this->mini .= $keyword;
                        break;
                    }
                case '"': {
                        $this->mini .= $this->readString('"');
                        break;
                    }
                case '\'': {
                        $this->mini .= $this->readString('\'');
                        break;
                    }
                default: {
                        if (
                            $this->previousToken === ';'
                            && $keyword === '}'
                        ) { // ;}
                            $this->previousToken = $keyword;
                            $this->mini[strlen($this->mini) - 1] = $keyword;
                            break;
                        }
                        $currentWhiteSpace = ctype_space($keyword);
                        $nextSafe = false;
                        if ($currentWhiteSpace) {
                            $keyword = ' ';
                            $nextSafe = $this->i + 1 >= $this->count || isset($this->safeTokensAfter[$this->css[$this->i + 1]]);
                        } else {
                            $this->previousToken = $keyword;
                        }
                        $currentSafe = isset($this->safeTokensBefore[$keyword]);
                        if (
                            !$currentWhiteSpace // not white space
                            || (!$this->previousWhiteSpace // white space after white space
                                && !$this->previousSafe // white space after not safe
                                && !$nextSafe) // white space before not safe
                        ) {
                            $this->mini .= $keyword;
                        }

                        $this->previousSafe = $currentSafe;
                        $this->previousWhiteSpace = $currentWhiteSpace;
                    }
            }

            $this->i++;
        }
        // print_r($this->mini);
        return $this->mini;
    }

    private function readComment()
    {
        $this->i += 2;
        while ($this->i < $this->count) {
            if (
                $this->css[$this->i] === '*' &&
                $this->i + 1 < $this->count &&
                $this->css[$this->i + 1] === '/'
            ) {
                $this->i += 2;
                return;
            }
            $this->i++;
        }
    }

    private function readString(string $quote)
    {
        $string = $quote;
        $this->i++;
        while ($this->i < $this->count) {
            if (
                $this->css[$this->i] === $quote &&
                $this->css[$this->i - 1] !== '\\'
            ) {
                $string .= $quote;
                return $string;
            }
            $string .= $this->css[$this->i];
            $this->i++;
        }
    }
}
