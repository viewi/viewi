function html_entity_decode(string, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.html-entity-decode.php
  return _php_html_unescape(string, flags, true)
}
