function convert_uuencode(str) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.convert-uuencode.php
  // Over the UTF-8 bytes, as PHP sees the string: lines of up to 45 bytes, each prefixed with its
  // length; every 3 bytes (zero-padded) become 4 characters, 0 written as `; ends with "`\n".
  const bytes = unescape(encodeURIComponent(_phpCastString(str)))
  if (bytes === '') {
    return ''
  }
  const enc = function (c) {
    return c === 0 ? '`' : String.fromCharCode(c + 32)
  }
  let out = ''
  for (let start = 0; start < bytes.length; start += 45) {
    const line = bytes.slice(start, start + 45)
    out += String.fromCharCode(line.length + 32)
    for (let i = 0; i < line.length; i += 3) {
      const a = line.charCodeAt(i)
      const b = i + 1 < line.length ? line.charCodeAt(i + 1) : 0
      const c = i + 2 < line.length ? line.charCodeAt(i + 2) : 0
      out += enc(a >> 2) + enc(((a << 4) | (b >> 4)) & 63) + enc(((b << 2) | (c >> 6)) & 63) + enc(c & 63)
    }
    out += '\n'
  }
  return out + '`\n'
}
