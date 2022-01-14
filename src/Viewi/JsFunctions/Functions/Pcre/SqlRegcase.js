function sql_regcase (str) { 
  var setlocale = window.setlocale
  var i = 0
  var upper = ''
  var lower = ''
  var pos = 0
  var retStr = ''
  setlocale('LC_ALL', 0)
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  upper = $locutus.php.locales[$locutus.php.localeCategories.LC_CTYPE].LC_CTYPE.upper
  lower = $locutus.php.locales[$locutus.php.localeCategories.LC_CTYPE].LC_CTYPE.lower
  for (i = 0; i < str.length; i++) {
    if (((pos = upper.indexOf(str.charAt(i))) !== -1) ||
      ((pos = lower.indexOf(str.charAt(i))) !== -1)) {
      retStr += '[' + upper.charAt(pos) + lower.charAt(pos) + ']'
    } else {
      retStr += str.charAt(i)
    }
  }
  return retStr
}
