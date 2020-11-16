function set_time_limit (seconds) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  setTimeout(function () {
    if (!$locutus.php.timeoutStatus) {
      $locutus.php.timeoutStatus = true
    }
    throw new Error('Maximum execution time exceeded')
  }, seconds * 1000)
}
