function array_splice(arr, offset, length, replacement) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-splice.php
  // arr is by-reference: it is rewritten in place. Returns the removed elements.
  // Integer keys are renumbered, string keys kept, in both the array and the result.
  const entries = _php_array_entries(arr)
  const n = entries.length
  offset = Math.trunc(offset) || 0
  const start = offset < 0 ? Math.max(n + offset, 0) : Math.min(offset, n)
  let end = length === undefined || length === null ? n : (length < 0 ? n + length : start + length)
  end = Math.min(Math.max(end, start), n)
  if (replacement === undefined || replacement === null) {
    replacement = []
  } else if (typeof replacement !== 'object') {
    replacement = [replacement]
  }
  const renumber = function (pairs) {
    let next = 0
    return pairs.map(function ([key, value]) {
      return [typeof key === 'number' ? next++ : key, value]
    })
  }
  const inserted = _php_array_entries(replacement).map(function ([, value]) {
    return [0, value] // renumbered below
  })
  const kept = entries.slice(0, start).concat(inserted, entries.slice(end))
  _php_array_set(arr, _php_array(renumber(kept)))
  return _php_array(renumber(entries.slice(start, end)))
}
