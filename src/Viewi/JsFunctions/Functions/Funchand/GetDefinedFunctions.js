function get_defined_functions () { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  var i = ''
  var arr = []
  var already = {}
  for (i in $global) {
    try {
      if (typeof $global[i] === 'function') {
        if (!already[i]) {
          already[i] = 1
          arr.push(i)
        }
      } else if (typeof $global[i] === 'object') {
        for (var j in $global[i]) {
          if (typeof $global[j] === 'function' && $global[j] && !already[j]) {
            already[j] = 1
            arr.push(j)
          }
        }
      }
    } catch (e) {
    }
  }
  return arr
}
