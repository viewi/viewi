function array_map(callback) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-map.php
  // One array: keys are kept. Several: zipped by position into a list, shorter ones padded with
  // null. A null callback returns the one array as is, or zips the arrays into rows.
  const arrays = Array.prototype.slice.call(arguments, 1).map(_php_array_entries)
  if (arrays.length === 1) {
    if (callback === null || callback === undefined) {
      return arguments[1]
    }
    return _php_array(arrays[0].map(function ([key, value]) {
      return [key, callback(value)]
    }))
  }
  const length = Math.max.apply(null, arrays.map(function (a) {
    return a.length
  }))
  const out = []
  for (let i = 0; i < length; i++) {
    const values = arrays.map(function (a) {
      return i < a.length ? a[i][1] : null
    })
    out.push(callback === null || callback === undefined ? values : callback.apply(null, values))
  }
  return out
}
