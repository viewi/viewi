function bcadd (leftOperand, rightOperand, scale) {
  var bc = window._bc
  var libbcmath = bc()
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
  result = libbcmath.bc_add(first, second, scale)
  if (result.n_scale > scale) {
    result.n_scale = scale
  }
  return result.toString()
}
