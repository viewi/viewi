function strtolower(str) {
  //  discuss at: https://www.php.net/manual/en/function.strtolower.php
  // ASCII only, as since PHP 8.2: 'ÉCOLE' becomes 'École'. Use mb_strtolower for Unicode.
  return _phpCastString(str).replace(/[A-Z]/g, function (c) {
    return String.fromCharCode(c.charCodeAt(0) + 32)
  })
}
