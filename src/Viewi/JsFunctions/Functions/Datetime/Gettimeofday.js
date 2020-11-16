function gettimeofday (returnFloat) {
  var t = new Date()
  var y = 0
  if (returnFloat) {
    return t.getTime() / 1000
  }
  y = t.getFullYear()
  return {
    sec: t.getUTCSeconds(),
    usec: t.getUTCMilliseconds() * 1000,
    minuteswest: t.getTimezoneOffset(),
    dsttime: 0 + (((new Date(y, 0)) - Date.UTC(y, 0)) !== ((new Date(y, 6)) - Date.UTC(y, 6)))
  }
}
