function fmod(x, y) {
  //  discuss at: https://www.php.net/manual/en/function.fmod.php
  // C's fmod: the remainder keeps the sign of x; fmod(x, 0) is NAN.
  return _php_cast_float(x) % _php_cast_float(y)
}
