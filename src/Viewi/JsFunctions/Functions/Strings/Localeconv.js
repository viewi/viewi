function localeconv () {
  var setlocale = window.setlocale
  var arr = {}
  var prop = ''
  setlocale('LC_ALL', 0)
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  for (prop in $locutus.php.locales[$locutus.php.localeCategories.LC_NUMERIC].LC_NUMERIC) {
    arr[prop] = $locutus.php.locales[$locutus.php.localeCategories.LC_NUMERIC].LC_NUMERIC[prop]
  }
  for (prop in $locutus.php.locales[$locutus.php.localeCategories.LC_MONETARY].LC_MONETARY) {
    arr[prop] = $locutus.php.locales[$locutus.php.localeCategories.LC_MONETARY].LC_MONETARY[prop]
  }
  return arr
}
