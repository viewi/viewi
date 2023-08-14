function explode (delimiter, string, limit) {
  if (arguments.length < 2 ||
    typeof delimiter === 'undefined' ||
    typeof string === 'undefined') {
    return null
  }
  if (delimiter === '' ||
    delimiter === false ||
    delimiter === null) {
    return false
  }
  if (typeof delimiter === 'function' ||
    typeof delimiter === 'object' ||
    typeof string === 'function' ||
    typeof string === 'object') {
    return {
      0: ''
    }
  }
  if (delimiter === true) {
    delimiter = '1'
  }
  delimiter += ''
  string += ''
  var s = string.split(delimiter)
  if (typeof limit === 'undefined') return s
  if (limit === 0) limit = 1
  if (limit > 0) {
    if (limit >= s.length) {
      return s
    }
    return s
      .slice(0, limit - 1)
      .concat([s.slice(limit - 1)
        .join(delimiter)
      ])
  }
  if (-limit >= s.length) {
    return []
  }
  s.splice(s.length + limit)
  return s
}
