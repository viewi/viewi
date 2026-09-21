function octdec(str) {
  //  discuss at: https://www.php.net/manual/en/function.octdec.php
  // Characters that are not base-8 digits are ignored, as PHP does; '' is 0.
  const digits = _phpCastString(str).replace(/[^01234567]/g, '')
  return digits === '' ? 0 : parseInt(digits, 8)
}
