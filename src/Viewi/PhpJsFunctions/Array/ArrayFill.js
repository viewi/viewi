function array_fill(startIndex, num, value) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-fill.php
  // Keys startIndex, startIndex + 1, … (PHP 8: also after a negative start).
  const pairs = []
  for (let i = 0; i < num; i++) {
    pairs.push([startIndex + i, value])
  }
  return _php_array(pairs)
}
