function printf () {
  var sprintf = require('../strings/sprintf')
  var echo = require('../strings/echo')
  var ret = sprintf.apply(this, arguments)
  echo(ret)
  return ret.length
}
