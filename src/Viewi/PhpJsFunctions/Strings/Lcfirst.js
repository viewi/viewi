function lcfirst(str) {
  //  discuss at: https://www.php.net/manual/en/function.lcfirst.php
  // ASCII only, as since PHP 8.2: 'ÉCOLE' stays 'ÉCOLE'.
  str = _phpCastString(str)
  return /^[A-Z]/.test(str) ? String.fromCharCode(str.charCodeAt(0) + 32) + str.slice(1) : str
}
