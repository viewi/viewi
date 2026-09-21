function intval(value, base) {
  //  discuss at: https://www.php.net/manual/en/function.intval.php
  // Base 10 (the default) is PHP's (int) cast. Other bases apply to strings only: an optional
  // 0x / 0o / 0b prefix matching the base is skipped, and base 0 picks the base from the prefix
  // ("0x1A" → 16, "012" → 8, "0b11" → 2).
  if (typeof value !== 'string' || base === undefined || base === 10) {
    return _php_cast_int(value)
  }
  const match = value.match(/^[ \t\n\r\v\f]*([+-]?)(0[xX]|0[oO]|0[bB]|0)?(.*)$/)
  const sign = match[1] === '-' ? -1 : 1
  const prefix = (match[2] || '').toLowerCase()
  let digits = match[3]
  if (base === 0) {
    base = prefix === '0x' ? 16 : (prefix === '0b' ? 2 : (prefix === '0o' || prefix === '0' ? 8 : 10))
  } else if (prefix && !((base === 16 && prefix === '0x') || (base === 2 && prefix === '0b') || (base === 8 && (prefix === '0o' || prefix === '0')))) {
    digits = match[2] + digits // the prefix is not a prefix in this base: "0" is a digit
  }
  const valid = '0123456789abcdefghijklmnopqrstuvwxyz'.slice(0, base)
  let result = 0
  for (const ch of digits.toLowerCase()) {
    const digit = valid.indexOf(ch)
    if (digit === -1) {
      break
    }
    result = result * base + digit
  }
  return sign * result
}
