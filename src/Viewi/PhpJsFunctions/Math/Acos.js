function acos (arg) {
  //  discuss at: https://locutus.io/php/acos/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //      note 1: Sorry about the crippled test. Needed because precision differs accross platforms.
  //   example 1: (acos(0.3) + '').substr(0, 17)
  //   returns 1: "1.266103672779499"

  return Math.acos(arg)
}
