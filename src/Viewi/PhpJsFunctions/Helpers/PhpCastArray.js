function _php_cast_array(value) { // eslint-disable-line camelcase
  // PHP's (array): null → [], an array stays as it is, an object → its properties, a scalar → [value].
  if (value === null || value === undefined) {
    return []
  }
  if (typeof value !== 'object') {
    return [value]
  }
  if (is_object(value)) {
    return Object.assign({}, value)
  }
  return value
}
