function call_user_func (cb, parameters) { 
  var callUserFuncArray = window.call_user_func_array
  parameters = Array.prototype.slice.call(arguments, 1)
  return callUserFuncArray(cb, parameters)
}
