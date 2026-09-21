function array_diff_assoc() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-diff-assoc.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, false, true, true, null, null)
}
