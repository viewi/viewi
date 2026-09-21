function array_intersect() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-intersect.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, true, true, false, null, null)
}
