function strpos(haystack, needle, offset) {
  //  discuss at: https://www.php.net/manual/en/function.strpos.php
  // A negative offset counts from the end; an offset outside the string throws, as PHP 8 does.
  haystack = _phpCastString(haystack)
  offset = Math.trunc(offset) || 0
  if (offset < 0) {
    offset += haystack.length
  }
  if (offset < 0 || offset > haystack.length) {
    throw new Error('strpos(): Argument #3 ($offset) must be contained in argument #1 ($haystack)')
  }
  const i = haystack.indexOf(_phpCastString(needle), offset)
  return i === -1 ? false : i
}
