function gmdate (format, timestamp) {
  var date = window.date
  var dt = typeof timestamp === 'undefined' ? new Date() 
    : timestamp instanceof Date ? new Date(timestamp) 
    : new Date(timestamp * 1000) 
  timestamp = Date.parse(dt.toUTCString().slice(0, -4)) / 1000
  return date(format, timestamp)
}
