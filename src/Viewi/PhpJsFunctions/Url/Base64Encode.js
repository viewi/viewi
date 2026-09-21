function base64_encode(str) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.base64-encode.php
  // Over the UTF-8 bytes, as PHP sees the string: base64_encode('é') is 'w6k='.
  const bytes = unescape(encodeURIComponent(_phpCastString(str)))
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
  let out = ''
  for (let i = 0; i < bytes.length; i += 3) {
    const a = bytes.charCodeAt(i)
    const b = i + 1 < bytes.length ? bytes.charCodeAt(i + 1) : 0
    const c = i + 2 < bytes.length ? bytes.charCodeAt(i + 2) : 0
    out += alphabet[a >> 2] + alphabet[((a & 3) << 4) | (b >> 4)] +
      (i + 1 < bytes.length ? alphabet[((b & 15) << 2) | (c >> 6)] : '=') +
      (i + 2 < bytes.length ? alphabet[c & 63] : '=')
  }
  return out
}
