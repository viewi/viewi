function uasort(arr, callback) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.uasort.php
  return _php_sort(arr, 0, { keepKeys: true, compare: callback })
}
