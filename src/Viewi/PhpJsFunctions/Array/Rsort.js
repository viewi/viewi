function rsort(arr, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.rsort.php
  return _php_sort(arr, flags, { reverse: true })
}
