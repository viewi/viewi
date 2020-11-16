function array_key_exists (key, search) { 
  if (!search || (search.constructor !== Array && search.constructor !== Object)) {
    return false
  }
  return key in search
}
