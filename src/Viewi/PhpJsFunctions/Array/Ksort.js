function ksort(arr, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.ksort.php
  return _php_sort(arr, flags, { keepKeys: true, byKey: true })
}
