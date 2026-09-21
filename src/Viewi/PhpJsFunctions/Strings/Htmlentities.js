function htmlentities(string, flags, encoding, doubleEncode) {
  //  discuss at: https://www.php.net/manual/en/function.htmlentities.php
  // Every character with a named HTML 4.01 entity (é → &eacute;, € → &euro;), quotes by flags.
  return _php_html_escape(string, flags, doubleEncode, true)
}
