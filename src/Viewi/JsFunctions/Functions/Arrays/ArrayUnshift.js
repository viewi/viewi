function array_unshift (array) { 
  var i = arguments.length
  while (--i !== 0) {
    arguments[0].unshift(arguments[i])
  }
  return arguments[0].length
}
