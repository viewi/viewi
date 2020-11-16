function strtok (str, tokens) {
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  if (tokens === undefined) {
    tokens = str
    str = $locutus.php.strtokleftOver
  }
  if (str.length === 0) {
    return false
  }
  if (tokens.indexOf(str.charAt(0)) !== -1) {
    return strtok(str.substr(1), tokens)
  }
  for (var i = 0; i < str.length; i++) {
    if (tokens.indexOf(str.charAt(i)) !== -1) {
      break
    }
  }
  $locutus.php.strtokleftOver = str.substr(i + 1)
  return str.substring(0, i)
}
