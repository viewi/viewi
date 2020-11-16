function array_column (input, ColumnKey, IndexKey = null) { 
  if (input !== null && (typeof input === 'object' || Array.isArray(input))) {
    var newarray = []
    if (typeof input === 'object') {
      let temparray = []
      for (let key of Object.keys(input)) {
        temparray.push(input[key])
      }
      input = temparray
    }
    if (Array.isArray(input)) {
      for (let key of input.keys()) {
        if (IndexKey && input[key][IndexKey]) {
          if (ColumnKey) {
            newarray[input[key][IndexKey]] = input[key][ColumnKey]
          } else {
            newarray[input[key][IndexKey]] = input[key]
          }
        } else {
          if (ColumnKey) {
            newarray.push(input[key][ColumnKey])
          } else {
            newarray.push(input[key])
          }
        }
      }
    }
    return Object.assign({}, newarray)
  }
}
