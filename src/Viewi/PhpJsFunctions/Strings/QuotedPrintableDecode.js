function quoted_printable_decode(str) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.quoted-printable-decode.php
  // =XX becomes that byte and soft breaks (=\r\n, =\n) vanish; the bytes are then read as UTF-8
  // (Latin-1 if they are not valid UTF-8), so =C3=A9 is 'é' as PHP prints it.
  const bytes = _phpCastString(str)
    .replace(/=\r?\n/g, '')
    .replace(/=([0-9A-Fa-f]{2})/g, function (m, hex) {
      return String.fromCharCode(parseInt(hex, 16))
    })
  try {
    return decodeURIComponent(escape(bytes))
  } catch (e) {
    return bytes
  }
}
