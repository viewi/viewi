function array_unique(arr, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-unique.php
  // Keeps the first of equal values with its key. flags: SORT_STRING 2 (default, compares
  // (string) casts), SORT_REGULAR 0 (PHP 8 ==), SORT_NUMERIC 1.
  flags = flags === undefined || flags === null ? 2 : flags
  const same = function (a, b) {
    if (flags === 0) {
      return _php_compare(a, b) === 0
    }
    if (flags === 1) {
      return _php_cast_float(a) === _php_cast_float(b)
    }
    return _phpCastString(a) === _phpCastString(b)
  }
  const kept = []
  for (const entry of _php_array_entries(arr)) {
    if (!kept.some(function (k) {
      return same(k[1], entry[1])
    })) {
      kept.push(entry)
    }
  }
  return _php_array(kept)
}
