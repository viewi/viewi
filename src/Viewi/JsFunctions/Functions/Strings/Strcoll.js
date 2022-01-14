function strcoll (str1, str2) {
  var setlocale = window.setlocale
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  setlocale('LC_ALL', 0) 
  var cmp = $locutus.php.locales[$locutus.php.localeCategories.LC_COLLATE].LC_COLLATE
  return cmp(str1, str2)
}
