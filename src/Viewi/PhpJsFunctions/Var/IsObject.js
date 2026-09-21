function is_object(value) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.is-object.php
  // A PHP array arrives as a JS array or plain object, so only class instances count.
  if (value === null || typeof value !== 'object' || Array.isArray(value)) {
    return false
  }
  const proto = Object.getPrototypeOf(value)
  return proto !== null && proto !== Object.prototype && proto.constructor.name !== 'Object'
}
