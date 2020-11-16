function hypot (x, y) {
  x = Math.abs(x)
  y = Math.abs(y)
  var t = Math.min(x, y)
  x = Math.max(x, y)
  t = t / x
  return x * Math.sqrt(1 + t * t) || null
}
