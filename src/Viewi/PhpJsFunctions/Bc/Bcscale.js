function bcscale(scale) {
  //  discuss at: https://www.php.net/manual/en/function.bcscale.php
  // PHP 8: returns the previous default scale; with no argument, just the current one.
  const libbcmath = _bc()
  const previous = libbcmath.scale || 0
  if (scale === undefined || scale === null) {
    return previous
  }
  scale = _php_cast_int(scale)
  if (scale < 0) {
    throw new Error('bcscale(): Argument #1 ($scale) must be between 0 and 2147483647')
  }
  libbcmath.scale = scale
  return previous
}
