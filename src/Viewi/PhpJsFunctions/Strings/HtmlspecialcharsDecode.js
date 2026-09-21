function htmlspecialchars_decode(string, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.htmlspecialchars-decode.php
  return _php_html_unescape(string, flags, false)
}
