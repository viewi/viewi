function max() {
  //  discuss at: https://www.php.net/manual/en/function.max.php
  // One array argument → its largest value; otherwise the largest argument. Compared the way
  // PHP 8 compares (_php_compare): max('10', '9') is '10', max(0, 'a') is 'a'. Among equals the
  // first one wins.
  const values = arguments.length === 1 && typeof arguments[0] === 'object' && arguments[0] !== null
    ? Object.values(arguments[0])
    : Array.prototype.slice.call(arguments)
  if (values.length === 0) {
    throw new Error('max(): Argument #1 ($value) must contain at least one element')
  }
  let best = values[0]
  for (let i = 1; i < values.length; i++) {
    if (_php_compare(values[i], best) > 0) {
      best = values[i]
    }
  }
  return best
}
