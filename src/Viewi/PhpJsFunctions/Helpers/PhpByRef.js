/**
 * The value a by-reference argument is passed as. PHP writes into the caller's variable; JS can
 * only fill an array or object in place, so a variable that holds none (undefined, null, a
 * scalar) gets a fresh [] first: preg_match($re, $s, $m) then fills $m. An existing array or
 * object is passed as is, so sort($list) still sorts $list. The transpiler emits
 * (m = _php_by_ref(m)) for every by-reference parameter of a PHP function.
 */
function _php_by_ref(value) { // eslint-disable-line camelcase
  return value !== null && typeof value === 'object' ? value : []
}
