function is_finite(value) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.is-finite.php
  return Number.isFinite(_php_cast_float(value))
}
