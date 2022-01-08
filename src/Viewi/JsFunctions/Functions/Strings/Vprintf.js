function vprintf (format, args) {
  var sprintf = sprintf
  var echo = console.log
  var ret = sprintf.apply(this, [format].concat(args))
  echo(ret)
  return ret.length
}
