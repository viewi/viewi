function vsprintf (format, args) {
  var sprintf = sprintf
  return sprintf.apply(this, [format].concat(args))
}
