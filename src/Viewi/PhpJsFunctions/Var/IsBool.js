function is_bool (mixedVar) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/is_bool/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  // improved by: CoursesWeb (https://www.coursesweb.net/)
  //   example 1: is_bool(false)
  //   returns 1: true
  //   example 2: is_bool(0)
  //   returns 2: false

  return (mixedVar === true || mixedVar === false) // Faster (in FF) than type checking
}
