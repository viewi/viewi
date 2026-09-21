function array_multisort() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-multisort.php
  // array_multisort($a, [SORT_ASC 4 | SORT_DESC 3], [flags], $b, …): sorts the rows by the first
  // array, ties by the next, and rewrites every array in place (by-reference). String keys stay,
  // integer keys are renumbered. Stable, as PHP 8.
  const columns = []
  for (let i = 0; i < arguments.length; i++) {
    const arg = arguments[i]
    if (arg !== null && typeof arg === 'object') {
      columns.push({ arr: arg, entries: _php_array_entries(arg), order: 4, flags: 0 })
    } else if (columns.length > 0) {
      if (arg === 3 || arg === 4) {
        columns[columns.length - 1].order = arg
      } else {
        columns[columns.length - 1].flags = arg
      }
    }
  }
  if (columns.length === 0) {
    return false
  }
  const size = columns[0].entries.length
  if (columns.some(function (c) {
    return c.entries.length !== size
  })) {
    throw new Error('array_multisort(): Array sizes are inconsistent')
  }
  const comparers = columns.map(function (c) {
    return _php_sort_compare(c.flags)
  })
  const rows = []
  for (let i = 0; i < size; i++) {
    rows.push(i)
  }
  rows.sort(function (x, y) {
    for (let c = 0; c < columns.length; c++) {
      const r = comparers[c](columns[c].entries[x][1], columns[c].entries[y][1])
      if (r !== 0) {
        return columns[c].order === 3 ? -r : r
      }
    }
    return 0
  })
  for (const column of columns) {
    let next = 0
    _php_array_set(column.arr, _php_array(rows.map(function (row) {
      const [key, value] = column.entries[row]
      return [typeof key === 'number' ? next++ : key, value]
    })))
  }
  return true
}
