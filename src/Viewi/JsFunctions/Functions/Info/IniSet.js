function ini_set (varname, newvalue) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.ini = $locutus.php.ini || {}
  $locutus.php.ini = $locutus.php.ini || {}
  $locutus.php.ini[varname] = $locutus.php.ini[varname] || {}
  var oldval = $locutus.php.ini[varname].local_value
  var lowerStr = (newvalue + '').toLowerCase().trim()
  if (newvalue === true || lowerStr === 'on' || lowerStr === '1') {
    newvalue = 'on'
  }
  if (newvalue === false || lowerStr === 'off' || lowerStr === '0') {
    newvalue = 'off'
  }
  var _setArr = function (oldval) {
    if (typeof oldval === 'undefined') {
      $locutus.ini[varname].local_value = []
    }
    $locutus.ini[varname].local_value.push(newvalue)
  }
  switch (varname) {
    case 'extension':
      _setArr(oldval, newvalue)
      break
    default:
      $locutus.php.ini[varname].local_value = newvalue
      break
  }
  return oldval
}
