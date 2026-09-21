function uksort(arr, callback) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.uksort.php
  return _php_sort(arr, 0, { keepKeys: true, byKey: true, compare: callback })
}
