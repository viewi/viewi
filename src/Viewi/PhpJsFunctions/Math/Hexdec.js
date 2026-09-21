function hexdec(str) {
  //  discuss at: https://www.php.net/manual/en/function.hexdec.php
  // Characters that are not base-16 digits are ignored, as PHP does; '' is 0.
  const digits = _phpCastString(str).replace(/[^0123456789abcdefABCDEF]/g, '')
  return digits === '' ? 0 : parseInt(digits, 16)
}
