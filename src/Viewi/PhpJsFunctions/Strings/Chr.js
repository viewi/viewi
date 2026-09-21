function chr(codePt) {
  //  discuss at: https://www.php.net/manual/en/function.chr.php
  // One byte, like PHP: the code wraps mod 256 (chr(321) is 'A', chr(-1) is chr(255)).
  // Codes 128-255 give the Latin-1 character where PHP gives a raw byte (bytes-vs-chars).
  return String.fromCharCode(((_php_cast_int(codePt) % 256) + 256) % 256)
}
