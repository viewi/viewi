function quoted_printable_encode(str) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.quoted-printable-encode.php
  // RFC 2045 over the UTF-8 bytes, as PHP sees the string: printable ASCII except = passes, the
  // rest is =XX; CRLF is kept; lines are soft-broken (=\r\n) before 76 characters.
  const bytes = unescape(encodeURIComponent(_phpCastString(str)))
  let out = ''
  let line = 0
  for (let i = 0; i < bytes.length; i++) {
    const code = bytes.charCodeAt(i)
    if (code === 13 && bytes.charCodeAt(i + 1) === 10) {
      out += '\r\n'
      line = 0
      i++
      continue
    }
    const trailingSpace = (code === 32 || code === 9) && (i + 1 === bytes.length || bytes.charCodeAt(i + 1) === 13)
    const piece = code === 61 || code < 32 || code > 126 || trailingSpace
      ? '=' + ('0' + code.toString(16).toUpperCase()).slice(-2)
      : bytes[i]
    if (line + piece.length > 75) {
      out += '=\r\n'
      line = 0
    }
    out += piece
    line += piece.length
  }
  return out
}
