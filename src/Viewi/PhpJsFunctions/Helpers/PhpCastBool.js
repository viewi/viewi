function _php_cast_bool(value) { // eslint-disable-line camelcase
  // PHP's (bool): false for false, 0, 0.0, '', '0', null and an empty array; true otherwise,
  // including '0.0', ' ', NAN and every object. JS's !! says true for '0' and [].
  if (value === null || value === undefined) {
    return false
  }
  switch (typeof value) {
    case 'boolean':
      return value
    case 'number':
      return value !== 0
    case 'string':
      return value !== '' && value !== '0'
    case 'object':
      if (Array.isArray(value)) {
        return value.length > 0
      }
      return is_object(value) || Object.keys(value).length > 0
  }
  return true
}
