function i18n_loc_set_default (name) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  $locutus.php.locales = $locutus.php.locales || {}
  $locutus.php.locales.en_US_POSIX = {
    sorting: function (str1, str2) {
      return (str1 === str2) ? 0 : ((str1 > str2) ? 1 : -1)
    }
  }
  $locutus.php.locale_default = name
  return true
}
