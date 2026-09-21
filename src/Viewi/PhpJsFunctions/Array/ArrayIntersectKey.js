function array_intersect_key() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-intersect-key.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, true, false, true, null, null)
}
