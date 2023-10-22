function is_binary (vr) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/is_binary/
  // original by: Brett Zamir (https://brett-zamir.me)
  //   example 1: is_binary('This could be binary as far as JavaScript knows...')
  //   returns 1: true

  return typeof vr === 'string' // If it is a string of any kind, it could be binary
}
