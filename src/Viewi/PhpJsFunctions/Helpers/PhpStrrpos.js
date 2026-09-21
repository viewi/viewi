/** The shared body of strrpos/strripos with PHP 8's offset rules. */
function _php_strrpos(haystack, needle, offset, fn) {
  offset = Math.trunc(offset) || 0
  const n = haystack.length
  if (offset > n || -offset > n) {
    throw new Error(fn + '(): Argument #3 ($offset) must be contained in argument #1 ($haystack)')
  }
  let i
  if (offset >= 0) {
    i = haystack.lastIndexOf(needle)
    return i !== -1 && i >= offset ? i : false
  }
  i = haystack.lastIndexOf(needle, -offset < needle.length ? n - needle.length : n + offset)
  return i === -1 ? false : i
}
