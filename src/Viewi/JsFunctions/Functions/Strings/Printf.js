function printf () {
  var sprintf = window.sprintf
  var echo = console.log
  var ret = sprintf.apply(this, arguments)
  echo(ret)
  return ret.length
}
