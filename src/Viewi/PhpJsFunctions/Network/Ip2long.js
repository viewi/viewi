function ip2long(ip) {
  //  discuss at: https://www.php.net/manual/en/function.ip2long.php
  // Only a full dotted quad of decimal 0-255 parts, as PHP 8 (inet_pton) accepts; false otherwise.
  const parts = _phpCastString(ip).split('.')
  if (parts.length !== 4 || !parts.every(function (p) {
    return /^(0|[1-9]\d{0,2})$/.test(p) && +p <= 255
  })) {
    return false
  }
  return ((+parts[0] * 256 + +parts[1]) * 256 + +parts[2]) * 256 + +parts[3]
}
