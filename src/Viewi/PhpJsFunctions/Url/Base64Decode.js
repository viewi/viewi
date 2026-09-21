function base64_decode(str, strict) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.base64-decode.php
  // Characters outside the alphabet are skipped (strict: false returned); missing padding is fine.
  // The bytes are read as UTF-8 ('w6k=' is 'é'), or Latin-1 when they are not valid UTF-8.
  str = _phpCastString(str)
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
  let bits = 0
  let count = 0
  let bytes = ''
  for (const ch of str) {
    const v = alphabet.indexOf(ch)
    if (v === -1) {
      if (strict && ch !== '=' && !/\s/.test(ch)) {
        return false
      }
      continue
    }
    bits = (bits << 6) | v
    count += 6
    if (count >= 8) {
      count -= 8
      bytes += String.fromCharCode((bits >> count) & 255)
    }
  }
  try {
    return decodeURIComponent(escape(bytes))
  } catch (e) {
    return bytes
  }
}
