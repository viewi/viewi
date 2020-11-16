function fmod (x, y) {
  var tmp
  var tmp2
  var p = 0
  var pY = 0
  var l = 0.0
  var l2 = 0.0
  tmp = x.toExponential().match(/^.\.?(.*)e(.+)$/)
  p = parseInt(tmp[2], 10) - (tmp[1] + '').length
  tmp = y.toExponential().match(/^.\.?(.*)e(.+)$/)
  pY = parseInt(tmp[2], 10) - (tmp[1] + '').length
  if (pY > p) {
    p = pY
  }
  tmp2 = (x % y)
  if (p < -100 || p > 20) {
    l = Math.round(Math.log(tmp2) / Math.log(10))
    l2 = Math.pow(10, l)
    return (tmp2 / l2).toFixed(l - p) * l2
  } else {
    return parseFloat(tmp2.toFixed(-p))
  }
}
