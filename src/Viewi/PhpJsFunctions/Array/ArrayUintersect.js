function array_uintersect() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-uintersect.php
  const arrays = Array.prototype.slice.call(arguments)
  const valueCompare = arrays.pop()
  return _php_set_op(arrays, true, true, false, valueCompare, null)
}
