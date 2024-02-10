/**
 * 
 * @param {string} regex 
 * @param {string} str 
 */
function preg_match(regex, str) { // eslint-disable-line camelcase
  // "#^/admin/blog/(.*)$#i"
  let flags = undefined;
  const packSymbol = regex[0];
  const parts = regex.split(packSymbol);
  const regExpression = parts[1];
  flags = parts[2];
  return (new RegExp(regExpression, flags).test(str));
}
