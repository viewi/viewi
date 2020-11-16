function log1p (x) {
  var ret = 0
  var n = 50
  if (x <= -1) {
    return '-INF'
  }
  if (x < 0 || x > 1) {
    return Math.log(1 + x)
  }
  for (var i = 1; i < n; i++) {
    ret += Math.pow(-x, i) / i
  }
  return -ret
}
