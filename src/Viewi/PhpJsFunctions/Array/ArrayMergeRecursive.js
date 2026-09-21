function array_merge_recursive() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-merge-recursive.php
  // Integer keys append; a repeated string key collects both values into an array (merging
  // recursively when both are arrays).
  const merge = function (a, b) {
    const pairs = _php_array_entries(a)
    let next = 0
    for (const entry of pairs) {
      if (typeof entry[0] === 'number' && entry[0] >= next) {
        next = entry[0] + 1
      }
    }
    for (const [key, value] of _php_array_entries(b)) {
      if (typeof key === 'number') {
        pairs.push([next++, value])
        continue
      }
      const at = pairs.findIndex(function (p) {
        return p[0] === key
      })
      if (at === -1) {
        pairs.push([key, value])
        continue
      }
      const old = pairs[at][1]
      const asArray = function (v) {
        return v !== null && typeof v === 'object' ? v : [v]
      }
      pairs[at] = [key, merge(asArray(old), asArray(value))]
    }
    return _php_array(pairs)
  }
  let result = []
  for (let i = 0; i < arguments.length; i++) {
    result = merge(result, arguments[i])
  }
  return result
}
