function array_merge() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-merge.php
  // Integer keys are renumbered from 0, string keys are kept and a later one overwrites.
  const pairs = []
  let next = 0
  for (let i = 0; i < arguments.length; i++) {
    for (const [key, value] of _php_array_entries(arguments[i])) {
      pairs.push([typeof key === 'number' ? next++ : key, value])
    }
  }
  return _php_array(pairs)
}
