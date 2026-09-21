function array_diff_key() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-diff-key.php
  const arrays = Array.prototype.slice.call(arguments)
  return _php_set_op(arrays, false, false, true, null, null)
}
