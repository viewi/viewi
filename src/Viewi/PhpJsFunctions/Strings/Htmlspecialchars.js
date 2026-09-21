function htmlspecialchars(string, flags, encoding, doubleEncode) {
  //  discuss at: https://www.php.net/manual/en/function.htmlspecialchars.php
  // & < > always; " and ' by flags. Since PHP 8.1 the default is ENT_QUOTES (' becomes &#039;).
  return _php_html_escape(string, flags, doubleEncode, false)
}
