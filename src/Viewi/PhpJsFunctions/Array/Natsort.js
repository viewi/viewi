function natsort(arr) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.natsort.php
  return _php_sort(arr, 6, { keepKeys: true })
}
