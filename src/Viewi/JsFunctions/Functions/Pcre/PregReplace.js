function preg_replace (pattern, replacement, string) { 
  var _flag = pattern.substr(pattern.lastIndexOf(pattern[0]) + 1)
  _flag = (_flag !== '') ? _flag : 'g'
  var _pattern = pattern.substr(1, pattern.lastIndexOf(pattern[0]) - 1)
  var regex = new RegExp(_pattern, _flag)
  var result = string.replace(regex, replacement)
  return result
}
