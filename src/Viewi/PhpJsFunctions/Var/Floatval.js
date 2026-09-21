function floatval(value) {
  //  discuss at: https://www.php.net/manual/en/function.floatval.php
  // PHP's (float): '1.5abc' → 1.5, true → 1, arrays → 0 or 1.
  return _php_cast_float(value)
}
