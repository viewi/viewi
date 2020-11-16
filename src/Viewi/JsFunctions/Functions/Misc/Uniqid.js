function uniqid (prefix, moreEntropy) {
  if (typeof prefix === 'undefined') {
    prefix = ''
  }
  var retId
  var _formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16) 
    if (reqWidth < seed.length) {
      return seed.slice(seed.length - reqWidth)
    }
    if (reqWidth > seed.length) {
      return Array(1 + (reqWidth - seed.length)).join('0') + seed
    }
    return seed
  }
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  if (!$locutus.php.uniqidSeed) {
    $locutus.php.uniqidSeed = Math.floor(Math.random() * 0x75bcd15)
  }
  $locutus.php.uniqidSeed++
  retId = prefix
  retId += _formatSeed(parseInt(new Date().getTime() / 1000, 10), 8)
  retId += _formatSeed($locutus.php.uniqidSeed, 5)
  if (moreEntropy) {
    retId += (Math.random() * 10).toFixed(8).toString()
  }
  return retId
}
