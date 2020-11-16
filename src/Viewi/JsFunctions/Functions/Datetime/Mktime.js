function mktime () {
  var d = new Date()
  var r = arguments
  var i = 0
  var e = ['Hours', 'Minutes', 'Seconds', 'Month', 'Date', 'FullYear']
  for (i = 0; i < e.length; i++) {
    if (typeof r[i] === 'undefined') {
      r[i] = d['get' + e[i]]()
      r[i] += (i === 3)
    } else {
      r[i] = parseInt(r[i], 10)
      if (isNaN(r[i])) {
        return false
      }
    }
  }
  r[5] += (r[5] >= 0 ? (r[5] <= 69 ? 2e3 : (r[5] <= 100 ? 1900 : 0)) : 0)
  d.setFullYear(r[5], r[3] - 1, r[4])
  d.setHours(r[0], r[1], r[2])
  var time = d.getTime()
  return (time / 1e3 >> 0) - (time < 0)
}
