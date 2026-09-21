function ucfirst(str) {
  //  discuss at: https://www.php.net/manual/en/function.ucfirst.php
  // ASCII only, as since PHP 8.2: 'école' stays 'école'.
  str = _phpCastString(str)
  return /^[a-z]/.test(str) ? String.fromCharCode(str.charCodeAt(0) - 32) + str.slice(1) : str
}
