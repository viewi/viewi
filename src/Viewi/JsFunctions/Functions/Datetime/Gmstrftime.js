function gmstrftime (format, timestamp) {
  var strftime = window.strftime
  var _date = (typeof timestamp === 'undefined')
    ? new Date()
    : (timestamp instanceof Date)
      ? new Date(timestamp)
      : new Date(timestamp * 1000)
  timestamp = Date.parse(_date.toUTCString().slice(0, -4)) / 1000
  return strftime(format, timestamp)
}
