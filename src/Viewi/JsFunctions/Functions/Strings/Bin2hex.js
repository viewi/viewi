function bin2hex (s) {
  var i
  var l
  var o = ''
  var n
  s += ''
  for (i = 0, l = s.length; i < l; i++) {
    n = s.charCodeAt(i)
      .toString(16)
    o += n.length < 2 ? '0' + n : n
  }
  return o
}
