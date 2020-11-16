function vprintf (format, args) {
  var sprintf = require('../strings/sprintf')
  var echo = require('../strings/echo')
  var ret = sprintf.apply(this, [format].concat(args))
  echo(ret)
  return ret.length
}
