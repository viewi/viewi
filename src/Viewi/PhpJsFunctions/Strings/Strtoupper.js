function strtoupper(str) {
  //  discuss at: https://www.php.net/manual/en/function.strtoupper.php
  // ASCII only, as since PHP 8.2: 'école' becomes 'éCOLE'. Use mb_strtoupper for Unicode.
  return _phpCastString(str).replace(/[a-z]/g, function (c) {
    return String.fromCharCode(c.charCodeAt(0) - 32)
  })
}
