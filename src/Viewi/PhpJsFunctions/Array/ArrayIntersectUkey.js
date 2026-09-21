function array_intersect_ukey() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-intersect-ukey.php
  const arrays = Array.prototype.slice.call(arguments)
  const keyCompare = arrays.pop()
  return _php_set_op(arrays, true, false, true, null, keyCompare)
}
