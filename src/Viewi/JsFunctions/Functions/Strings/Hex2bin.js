function hex2bin (s) {
  var ret = []
  var i = 0
  var l
  s += ''
  for (l = s.length; i < l; i += 2) {
    var c = parseInt(s.substr(i, 1), 16)
    var k = parseInt(s.substr(i + 1, 1), 16)
    if (isNaN(c) || isNaN(k)) return false
    ret.push((c << 4) | k)
  }
  return String.fromCharCode.apply(String, ret)
}
