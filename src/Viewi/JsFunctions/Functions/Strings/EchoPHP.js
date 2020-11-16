function echo () {
  var args = Array.prototype.slice.call(arguments)
  return console.log(args.join(' '))
}
