function bcscale (scale) {
  var _bc = require('../_helpers/_bc')
  var libbcmath = _bc()
  scale = parseInt(scale, 10)
  if (isNaN(scale)) {
    return false
  }
  if (scale < 0) {
    return false
  }
  libbcmath.scale = scale
  return true
}
