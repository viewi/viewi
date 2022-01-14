function bccomp (leftOperand, rightOperand, scale) {
  var bc = window._bc
  var libbcmath = bc()
  var first, second
  if (typeof scale === 'undefined') {
    scale = libbcmath.scale
  }
  scale = ((scale < 0) ? 0 : scale)
  first = libbcmath.bc_init_num()
  second = libbcmath.bc_init_num()
  first = libbcmath.bc_str2num(leftOperand.toString(), scale)
  second = libbcmath.bc_str2num(rightOperand.toString(), scale)
  return libbcmath.bc_compare(first, second, scale)
}
