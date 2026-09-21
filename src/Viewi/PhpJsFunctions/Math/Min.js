function min() {
  //  discuss at: https://www.php.net/manual/en/function.min.php
  // One array argument → its smallest value; otherwise the smallest argument. Compared the way
  // PHP 8 compares (_php_compare): min('10', '9') is '9', min(-1, null) is null. Among equals the
  // first one wins.
  const values = arguments.length === 1 && typeof arguments[0] === 'object' && arguments[0] !== null
    ? Object.values(arguments[0])
    : Array.prototype.slice.call(arguments)
  if (values.length === 0) {
    throw new Error('min(): Argument #1 ($value) must contain at least one element')
  }
  let best = values[0]
  for (let i = 1; i < values.length; i++) {
    if (_php_compare(values[i], best) < 0) {
      best = values[i]
    }
  }
  return best
}
