function pos (arr) {
  //  discuss at: https://locutus.io/php/pos/
  // original by: Brett Zamir (https://brett-zamir.me)
  //      note 1: Uses global: locutus to store the array pointer
  //   example 1: var $transport = ['foot', 'bike', 'car', 'plane']
  //   example 1: pos($transport)
  //   returns 1: 'foot'


  return current(arr)
}
