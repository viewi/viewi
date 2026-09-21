function array_reverse(arr, preserveKeys) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-reverse.php
  // String keys are kept; integer keys are renumbered unless preserveKeys.
  let next = 0
  return _php_array(_php_array_entries(arr).reverse().map(function ([key, value]) {
    return [typeof key === 'number' && !preserveKeys ? next++ : key, value]
  }))
}
