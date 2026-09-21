function array_diff() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-diff.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, false, true, false, null, null)
}
