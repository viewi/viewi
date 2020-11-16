function inet_ntop (a) { 
  var i = 0
  var m = ''
  var c = []
  a += ''
  if (a.length === 4) {
    return [
      a.charCodeAt(0),
      a.charCodeAt(1),
      a.charCodeAt(2),
      a.charCodeAt(3)
    ].join('.')
  } else if (a.length === 16) {
    for (i = 0; i < 16; i++) {
      c.push(((a.charCodeAt(i++) << 8) + a.charCodeAt(i)).toString(16))
    }
    return c.join(':')
      .replace(/((^|:)0(?=:|$))+:?/g, function (t) {
        m = (t.length > m.length) ? t : m
        return t
      })
      .replace(m || ' ', '::')
  } else {
    return false
  }
}
