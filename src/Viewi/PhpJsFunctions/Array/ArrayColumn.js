function array_column(rows, columnKey, indexKey) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-column.php
  // columnKey null takes the whole row; rows without the column are skipped; indexKey (when the
  // row has it) becomes the key, otherwise the next integer.
  const pairs = []
  let next = 0
  for (const [, row] of _php_array_entries(rows)) {
    if (row === null || typeof row !== 'object') {
      continue
    }
    if (columnKey !== null && columnKey !== undefined && !Object.prototype.hasOwnProperty.call(row, columnKey)) {
      continue
    }
    const value = columnKey === null || columnKey === undefined ? row : row[columnKey]
    const hasIndex = indexKey !== null && indexKey !== undefined && Object.prototype.hasOwnProperty.call(row, indexKey)
    if (hasIndex) {
      const key = _php_array_key(_phpCastString(row[indexKey]))
      pairs.push([key, value])
      if (typeof key === 'number' && key >= next) {
        next = key + 1
      }
    } else {
      pairs.push([next++, value])
    }
  }
  return _php_array(pairs)
}
