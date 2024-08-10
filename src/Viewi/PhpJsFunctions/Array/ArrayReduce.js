function array_reduce(aInput, callback, initial) { // eslint-disable-line camelcase
  let result = initial === undefined ? 0 : initial;
  if (Array.isArray(aInput)) {
    for (i = 0; i < aInput.length; i++) {
      result = callback.apply(null, [result, aInput[i]]);
    }
  } else {
    for (let k in aInput) {
      result = callback.apply(null, [result, aInput[k]]);
    }
  }
  return result;
}
