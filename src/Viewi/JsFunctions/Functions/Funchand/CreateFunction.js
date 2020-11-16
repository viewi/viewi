function create_function (args, code) { 
  try {
    return Function.apply(null, args.split(',').concat(code))
  } catch (e) {
    return false
  }
}
