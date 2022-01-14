function convert_uuencode (str) { 
  var isScalar = window.is_scalar
  var chr = function (c) {
    return String.fromCharCode(c)
  }
  if (!str || str === '') {
    return chr(0)
  } else if (!isScalar(str)) {
    return false
  }
  var c = 0
  var u = 0
  var i = 0
  var a = 0
  var encoded = ''
  var tmp1 = ''
  var tmp2 = ''
  var bytes = {}
  var chunk = function () {
    bytes = str.substr(u, 45).split('')
    for (i in bytes) {
      bytes[i] = bytes[i].charCodeAt(0)
    }
    return bytes.length || 0
  }
  while ((c = chunk()) !== 0) {
    u += 45
    encoded += chr(c + 32)
    for (i in bytes) {
      tmp1 = bytes[i].toString(2)
      while (tmp1.length < 8) {
        tmp1 = '0' + tmp1
      }
      tmp2 += tmp1
    }
    while (tmp2.length % 6) {
      tmp2 = tmp2 + '0'
    }
    for (i = 0; i <= (tmp2.length / 6) - 1; i++) {
      tmp1 = tmp2.substr(a, 6)
      if (tmp1 === '000000') {
        encoded += chr(96)
      } else {
        encoded += chr(parseInt(tmp1, 2) + 32)
      }
      a += 6
    }
    a = 0
    tmp2 = ''
    encoded += '\n'
  }
  encoded += chr(96) + '\n'
  return encoded
}
