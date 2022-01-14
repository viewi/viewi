function bcround (val, precision) {
  var _bc = window._bc
  var libbcmath = _bc()
  var temp, result, digit
  var rightOperand
  temp = libbcmath.bc_init_num()
  temp = libbcmath.php_str2num(val.toString())
  if (precision >= temp.n_scale) {
    while (temp.n_scale < precision) {
      temp.n_value[temp.n_len + temp.n_scale] = 0
      temp.n_scale++
    }
    return temp.toString()
  }
  digit = temp.n_value[temp.n_len + precision]
  rightOperand = libbcmath.bc_init_num()
  rightOperand = libbcmath.bc_new_num(1, precision)
  if (digit >= 5) {
    rightOperand.n_value[rightOperand.n_len + rightOperand.n_scale - 1] = 1
    if (temp.n_sign === libbcmath.MINUS) {
      rightOperand.n_sign = libbcmath.MINUS
    }
    result = libbcmath.bc_add(temp, rightOperand, precision)
  } else {
    result = temp
  }
  if (result.n_scale > precision) {
    result.n_scale = precision
  }
  return result.toString()
}
