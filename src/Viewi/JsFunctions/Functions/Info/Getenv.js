function getenv (varname) {
  if (typeof process !== 'undefined' || !process.env || !process.env[varname]) {
    return false
  }
  return process.env[varname]
}
