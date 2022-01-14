function vsprintf (format, args) {
  var sprintf = window.sprintf
  return sprintf.apply(this, [format].concat(args))
}
