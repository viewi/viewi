function arsort(arr, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.arsort.php
  return _php_sort(arr, flags, { keepKeys: true, reverse: true })
}
