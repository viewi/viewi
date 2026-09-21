function asort(arr, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.asort.php
  return _php_sort(arr, flags, { keepKeys: true })
}
