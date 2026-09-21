function substr_count(haystack, needle, offset, length) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.substr-count.php
  // Non-overlapping, as PHP: substr_count('aaa', 'aa') is 1. offset/length may be negative.
  haystack = _phpCastString(haystack)
  needle = _phpCastString(needle)
  if (needle === '') {
    throw new Error('substr_count(): Argument #2 ($needle) cannot be empty')
  }
  offset = Math.trunc(offset) || 0
  if (offset < 0) {
    offset += haystack.length
  }
  if (offset < 0 || offset > haystack.length) {
    throw new Error('substr_count(): Argument #3 ($offset) must be contained in argument #1 ($haystack)')
  }
  let end = haystack.length
  if (length !== undefined && length !== null) {
    end = length < 0 ? haystack.length + Math.trunc(length) : offset + Math.trunc(length)
    if (end < offset || end > haystack.length) {
      throw new Error('substr_count(): Argument #4 ($length) must be contained in argument #1 ($haystack)')
    }
  }
  const part = haystack.slice(offset, end)
  let count = 0
  let pos = part.indexOf(needle)
  while (pos !== -1) {
    count++
    pos = part.indexOf(needle, pos + needle.length)
  }
  return count
}
