function bcmul (leftOperand, rightOperand, scale) {
  var _bc = window._bc
  var libbcmath = _bc()
  var first, second, result
  if (typeof scale === 'undefined') {
    scale = libbcmath.scale
  }
  scale = ((scale < 0) ? 0 : scale)
  first = libbcmath.bc_init_num()
  second = libbcmath.bc_init_num()
  result = libbcmath.bc_init_num()
  first = libbcmath.php_str2num(leftOperand.toString())
  second = libbcmath.php_str2num(rightOperand.toString())
  result = libbcmath.bc_multiply(first, second, scale)
  if (result.n_scale > scale) {
    result.n_scale = scale
  }
  return result.toString()
}
