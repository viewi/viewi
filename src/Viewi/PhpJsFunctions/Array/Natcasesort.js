function natcasesort(arr) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.natcasesort.php
  return _php_sort(arr, 6 | 8, { keepKeys: true })
}
