function trim(str, charlist) {
  //  discuss at: https://www.php.net/manual/en/function.trim.php
  // PHP's default set: space, \t, \n, \r, \0, \x0B — no other Unicode whitespace.
  // charlist supports ranges: 'a..z'.
  return _php_trim(str, charlist, 3)
}
