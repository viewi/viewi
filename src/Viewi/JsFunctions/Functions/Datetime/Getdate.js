function getdate (timestamp) {
  var _w = [
    'Sun',
    'Mon',
    'Tues',
    'Wednes',
    'Thurs',
    'Fri',
    'Satur'
  ]
  var _m = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ]
  var d = ((typeof timestamp === 'undefined') ? new Date()
    : (timestamp instanceof Date) ? new Date(timestamp)  
    : new Date(timestamp * 1000) 
  )
  var w = d.getDay()
  var m = d.getMonth()
  var y = d.getFullYear()
  var r = {}
  r.seconds = d.getSeconds()
  r.minutes = d.getMinutes()
  r.hours = d.getHours()
  r.mday = d.getDate()
  r.wday = w
  r.mon = m + 1
  r.year = y
  r.yday = Math.floor((d - (new Date(y, 0, 1))) / 86400000)
  r.weekday = _w[w] + 'day'
  r.month = _m[m]
  r['0'] = parseInt(d.getTime() / 1000, 10)
  return r
}
