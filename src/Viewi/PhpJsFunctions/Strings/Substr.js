function substr(input, start, length) {
  //  discuss at: https://www.php.net/manual/en/function.substr.php
  // PHP 8 semantics: always a string ("" where PHP 7 returned false); a start past the end or
  // a length that ends before the start gives ""; null length means "to the end".
  // Counts JS characters where PHP counts bytes (see the bytes-vs-chars known difference).
  input = _phpCastString(input)
  const n = input.length
  start = Math.trunc(start) || 0
  if (start < 0) {
    start = Math.max(n + start, 0)
  }
  if (start > n) {
    return ''
  }
  let end = n
  if (length !== undefined && length !== null) {
    length = Math.trunc(length) || 0
    end = length < 0 ? n + length : start + length
  }
  return end <= start ? '' : input.slice(start, Math.min(end, n))
}
