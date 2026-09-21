function array_shift(arr) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-shift.php
  // arr is by-reference: rewritten in place, integer keys renumbered. null when empty.
  const entries = _php_array_entries(arr)
  if (entries.length === 0) {
    return null
  }
  let next = 0
  _php_array_set(arr, _php_array(entries.slice(1).map(function ([key, value]) {
    return [typeof key === 'number' ? next++ : key, value]
  })))
  return entries[0][1]
}
