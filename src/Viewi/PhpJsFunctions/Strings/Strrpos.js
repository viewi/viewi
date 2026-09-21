function strrpos(haystack, needle, offset) {
  //  discuss at: https://www.php.net/manual/en/function.strrpos.php
  // Last occurrence. A positive offset only accepts matches at or after it; a negative one ends
  // the search that far from the end (strrpos('hello', 'l', -3) is 2). Out of range throws.
  return _php_strrpos(_phpCastString(haystack), _phpCastString(needle), offset, 'strrpos')
}
