function strripos(haystack, needle, offset) {
  //  discuss at: https://www.php.net/manual/en/function.strripos.php
  // strrpos, ignoring ASCII case.
  const lower = function (s) {
    return _phpCastString(s).replace(/[A-Z]/g, function (c) {
      return String.fromCharCode(c.charCodeAt(0) + 32)
    })
  }
  return _php_strrpos(lower(haystack), lower(needle), offset, 'strripos')
}
