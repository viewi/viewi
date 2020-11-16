function idate (format, timestamp) {
  if (format === undefined) {
    throw new Error('idate() expects at least 1 parameter, 0 given')
  }
  if (!format.length || format.length > 1) {
    throw new Error('idate format is one char')
  }
  var _date = (typeof timestamp === 'undefined')
    ? new Date()
    : (timestamp instanceof Date)
      ? new Date(timestamp)
      : new Date(timestamp * 1000)
  var a
  switch (format) {
    case 'B':
      return Math.floor((
        (_date.getUTCHours() * 36e2) +
        (_date.getUTCMinutes() * 60) +
        _date.getUTCSeconds() + 36e2
      ) / 86.4) % 1e3
    case 'd':
      return _date.getDate()
    case 'h':
      return _date.getHours() % 12 || 12
    case 'H':
      return _date.getHours()
    case 'i':
      return _date.getMinutes()
    case 'I':
      a = _date.getFullYear()
      return 0 + (((new Date(a, 0)) - Date.UTC(a, 0)) !== ((new Date(a, 6)) - Date.UTC(a, 6)))
    case 'L':
      a = _date.getFullYear()
      return (!(a & 3) && (a % 1e2 || !(a % 4e2))) ? 1 : 0
    case 'm':
      return _date.getMonth() + 1
    case 's':
      return _date.getSeconds()
    case 't':
      return (new Date(_date.getFullYear(), _date.getMonth() + 1, 0))
      .getDate()
    case 'U':
      return Math.round(_date.getTime() / 1000)
    case 'w':
      return _date.getDay()
    case 'W':
      a = new Date(
        _date.getFullYear(),
        _date.getMonth(),
        _date.getDate() - (_date.getDay() || 7) + 3
      )
      return 1 + Math.round((a - (new Date(a.getFullYear(), 0, 4))) / 864e5 / 7)
    case 'y':
      return parseInt((_date.getFullYear() + '')
      .slice(2), 10) 
    case 'Y':
      return _date.getFullYear()
    case 'z':
      return Math.floor((_date - new Date(_date.getFullYear(), 0, 1)) / 864e5)
    case 'Z':
      return -_date.getTimezoneOffset() * 60
    default:
      throw new Error('Unrecognized _date format token')
  }
}
