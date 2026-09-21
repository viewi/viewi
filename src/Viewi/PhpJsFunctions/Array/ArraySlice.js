function array_slice(arr, offset, length, preserveKeys) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-slice.php
  // Integer keys are renumbered unless preserveKeys; string keys are always kept.
  const entries = _php_array_entries(arr)
  const n = entries.length
  offset = Math.trunc(offset) || 0
  const start = offset < 0 ? Math.max(n + offset, 0) : Math.min(offset, n)
  let end = length === undefined || length === null ? n : (length < 0 ? n + length : start + length)
  end = Math.min(Math.max(end, start), n)
  let next = 0
  return _php_array(entries.slice(start, end).map(function ([key, value]) {
    return [typeof key === 'number' && !preserveKeys ? next++ : key, value]
  }))
}
