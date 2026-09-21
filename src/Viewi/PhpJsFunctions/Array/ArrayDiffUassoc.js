function array_diff_uassoc() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-diff-uassoc.php
  const arrays = Array.prototype.slice.call(arguments)
  const keyCompare = arrays.pop()
  return _php_set_op(arrays, false, true, true, null, keyCompare)
}
