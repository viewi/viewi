function get_defined_functions () { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/get_defined_functions/
  // original by: Brett Zamir (https://brett-zamir.me)
  // improved by: Brett Zamir (https://brett-zamir.me)
  //      note 1: Test case 1: If get_defined_functions can find
  //      note 1: itself in the defined functions, it worked :)
  //   example 1: function test_in_array (array, p_val) {for(var i = 0, l = array.length; i < l; i++) {if (array[i] === p_val) return true} return false}
  //   example 1: var $funcs = get_defined_functions()
  //   example 1: var $found = test_in_array($funcs, 'get_defined_functions')
  //   example 1: var $result = $found
  //   returns 1: true
  //        test: skip-1

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}

  let i = ''
  const arr = []
  const already = {}

  for (i in $global) {
    try {
      if (typeof $global[i] === 'function') {
        if (!already[i]) {
          already[i] = 1
          arr.push(i)
        }
      } else if (typeof $global[i] === 'object') {
        for (const j in $global[i]) {
          if (typeof $global[j] === 'function' && $global[j] && !already[j]) {
            already[j] = 1
            arr.push(j)
          }
        }
      }
    } catch (e) {
      // Some objects in Firefox throw exceptions when their
      // properties are accessed (e.g., sessionStorage)
    }
  }

  return arr
}
