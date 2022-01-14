function strval (str) {
  var gettype = window.gettype
  var type = ''
  if (str === null) {
    return ''
  }
  type = gettype(str)
  switch (type) {
    case 'boolean':
      if (str === true) {
        return '1'
      }
      return ''
    case 'array':
      return 'Array'
    case 'object':
      return 'Object'
  }
  return str
}
