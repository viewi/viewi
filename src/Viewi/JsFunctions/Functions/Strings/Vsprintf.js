function vsprintf (format, args) {
  var sprintf = require('../strings/sprintf')
  return sprintf.apply(this, [format].concat(args))
}
