function array_reduce(arr, callback, initial) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-reduce.php
  let carry = initial === undefined ? null : initial
  for (const entry of _php_array_entries(arr)) {
    carry = callback(carry, entry[1])
  }
  return carry
}
