function array_filter(arr, func, mode) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-filter.php
  // Keys are preserved, as in PHP: filtering [1, 0, 2] leaves {0: 1, 2: 2}, not a list.
  // mode: ARRAY_FILTER_USE_BOTH (1) passes (value, key), ARRAY_FILTER_USE_KEY (2) passes key.
  const truthy = function (v) {
    if (v === null || v === undefined || v === false || v === 0 || v === '' || v === '0') {
      return false
    }
    return typeof v !== 'object' || Object.keys(v).length > 0
  }
  const kept = []
  for (const [key, value] of _php_array_entries(arr)) {
    let keep = value
    if (typeof func === 'function') {
      keep = mode === 2 ? func(key) : (mode === 1 ? func(value, key) : func(value))
    }
    if (truthy(keep)) {
      kept.push([key, value])
    }
  }
  return _php_array(kept)
}
