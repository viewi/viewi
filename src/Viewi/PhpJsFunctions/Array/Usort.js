function usort(arr, callback) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.usort.php
  return _php_sort(arr, 0, { compare: callback })
}
