function json_last_error () { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  return $locutus.php && $locutus.php.last_error_json ? $locutus.php.last_error_json : 0
}
