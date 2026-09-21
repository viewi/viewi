function pow(base, exp) {
  //  discuss at: https://www.php.net/manual/en/function.pow.php
  // Numeric strings count as numbers ('3' ** 2 is 9); no rounding of the result.
  const toNumber = function (v) {
    return typeof v === 'number' ? v : _php_cast_float(v)
  }
  return Math.pow(toNumber(base), toNumber(exp))
}
