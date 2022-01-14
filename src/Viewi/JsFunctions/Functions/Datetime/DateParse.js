function date_parse (date) { 
  var strtotime = window.strtotime
  var ts
  try {
    ts = strtotime(date)
  } catch (e) {
    ts = false
  }
  if (!ts) {
    return false
  }
  var dt = new Date(ts * 1000)
  var retObj = {}
  retObj.year = dt.getFullYear()
  retObj.month = dt.getMonth() + 1
  retObj.day = dt.getDate()
  retObj.hour = dt.getHours()
  retObj.minute = dt.getMinutes()
  retObj.second = dt.getSeconds()
  retObj.fraction = parseFloat('0.' + dt.getMilliseconds())
  retObj.is_localtime = dt.getTimezoneOffset() !== 0
  return retObj
}
