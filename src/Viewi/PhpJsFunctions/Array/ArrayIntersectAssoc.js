function array_intersect_assoc() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-intersect-assoc.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, true, true, true, null, null)
}
