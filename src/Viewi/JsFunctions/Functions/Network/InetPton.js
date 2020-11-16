function inet_pton (a) { 
  var m
  var i
  var j
  var f = String.fromCharCode
  m = a.match(/^(?:\d{1,3}(?:\.|$)){4}/)
  if (m) {
    m = m[0].split('.')
    m = f(m[0], m[1], m[2], m[3])
    return m.length === 4 ? m : false
  }
  if (a.length > 39) {
    return false
  }
  m = a.split('::')
  if (m.length > 2) {
    return false
  } 
  const reHexDigits = /^[\da-f]{1,4}$/i
  for (j = 0; j < m.length; j++) {
    if (m[j].length === 0) { 
      continue
    }
    m[j] = m[j].split(':')
    for (i = 0; i < m[j].length; i++) {
      let hextet = m[j][i]
      if (!reHexDigits.test(hextet)) {
        return false
      }
      hextet = parseInt(hextet, 16)
      if (isNaN(hextet)) {
        return false
      }
      m[j][i] = f(hextet >> 8, hextet & 0xFF)
    }
    m[j] = m[j].join('')
  }
  return m.join('\x00'.repeat(16 - m.reduce((tl, m) => tl + m.length, 0)))
}
