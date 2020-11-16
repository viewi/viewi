function _bc () { 
  var Libbcmath = {
    PLUS: '+',
    MINUS: '-',
    BASE: 10,
    scale: 0,
    bc_num: function () {
      this.n_sign = null 
      this.n_len = null 
      this.n_scale = null 
      this.n_value = null 
      this.toString = function () {
        var r, tmp
        tmp = this.n_value.join('')
        r = ((this.n_sign === Libbcmath.PLUS) ? '' : this.n_sign) + tmp.substr(0, this.n_len)
        if (this.n_scale > 0) {
          r += '.' + tmp.substr(this.n_len, this.n_scale)
        }
        return r
      }
    },
    bc_add: function (n1, n2, scaleMin) {
      var sum, cmpRes, resScale
      if (n1.n_sign === n2.n_sign) {
        sum = Libbcmath._bc_do_add(n1, n2, scaleMin)
        sum.n_sign = n1.n_sign
      } else { 
        cmpRes = Libbcmath._bc_do_compare(n1, n2, false, false) 
        switch (cmpRes) {
          case -1:
            sum = Libbcmath._bc_do_sub(n2, n1, scaleMin)
            sum.n_sign = n2.n_sign
            break
          case 0:
            resScale = Libbcmath.MAX(scaleMin, Libbcmath.MAX(n1.n_scale, n2.n_scale))
            sum = Libbcmath.bc_new_num(1, resScale)
            Libbcmath.memset(sum.n_value, 0, 0, resScale + 1)
            break
          case 1:
            sum = Libbcmath._bc_do_sub(n1, n2, scaleMin)
            sum.n_sign = n1.n_sign
        }
      }
      return sum
    },
    bc_compare: function (n1, n2) {
      return Libbcmath._bc_do_compare(n1, n2, true, false)
    },
    _one_mult: function (num, nPtr, size, digit, result, rPtr) {
      var carry, value 
      var nptr, rptr 
      if (digit === 0) {
        Libbcmath.memset(result, 0, 0, size) 
      } else {
        if (digit === 1) {
          Libbcmath.memcpy(result, rPtr, num, nPtr, size) 
        } else { 
          nptr = nPtr + size - 1 
          rptr = rPtr + size - 1 
          carry = 0
          while (size-- > 0) {
            value = num[nptr--] * digit + carry 
            result[rptr--] = value % Libbcmath.BASE 
            carry = Math.floor(value / Libbcmath.BASE) 
          }
          if (carry !== 0) {
            result[rptr] = carry
          }
        }
      }
    },
    bc_divide: function (n1, n2, scale) {
      var qval 
      var num1, num2 
      var ptr1, ptr2, n2ptr, qptr 
      var scale1, val 
      var len1, len2, scale2, qdigits, extra, count 
      var qdig, qguess, borrow, carry 
      var mval 
      var zero 
      var norm 
      if (Libbcmath.bc_is_zero(n2)) {
        return -1
      }
      if (Libbcmath.bc_is_zero(n1)) {
        return Libbcmath.bc_new_num(1, scale)
      }
      if (n2.n_scale === 0) {
        if (n2.n_len === 1 && n2.n_value[0] === 1) {
          qval = Libbcmath.bc_new_num(n1.n_len, scale) 
          qval.n_sign = (n1.n_sign === n2.n_sign ? Libbcmath.PLUS : Libbcmath.MINUS)
          Libbcmath.memset(qval.n_value, n1.n_len, 0, scale)
          Libbcmath.memcpy(
            qval.n_value, 0, n1.n_value, 0, n1.n_len + Libbcmath.MIN(n1.n_scale, scale)
          )
        }
      }
      scale2 = n2.n_scale 
      n2ptr = n2.n_len + scale2 - 1 
      while ((scale2 > 0) && (n2.n_value[n2ptr--] === 0)) {
        scale2--
      }
      len1 = n1.n_len + scale2
      scale1 = n1.n_scale - scale2
      if (scale1 < scale) {
        extra = scale - scale1
      } else {
        extra = 0
      }
      num1 = Libbcmath.safe_emalloc(1, n1.n_len + n1.n_scale, extra + 2)
      if (num1 === null) {
        Libbcmath.bc_out_of_memory()
      }
      Libbcmath.memset(num1, 0, 0, n1.n_len + n1.n_scale + extra + 2)
      Libbcmath.memcpy(num1, 1, n1.n_value, 0, n1.n_len + n1.n_scale)
      len2 = n2.n_len + scale2
      num2 = Libbcmath.safe_emalloc(1, len2, 1)
      if (num2 === null) {
        Libbcmath.bc_out_of_memory()
      }
      Libbcmath.memcpy(num2, 0, n2.n_value, 0, len2)
      num2[len2] = 0
      n2ptr = 0
      while (num2[n2ptr] === 0) {
        n2ptr++
        len2--
      }
      if (len2 > len1 + scale) {
        qdigits = scale + 1
        zero = true
      } else {
        zero = false
        if (len2 > len1) {
          qdigits = scale + 1 
        } else {
          qdigits = len1 - len2 + scale + 1
        }
      }
      qval = Libbcmath.bc_new_num(qdigits - scale, scale)
      Libbcmath.memset(qval.n_value, 0, 0, qdigits)
      mval = Libbcmath.safe_emalloc(1, len2, 1)
      if (mval === null) {
        Libbcmath.bc_out_of_memory()
      }
      if (!zero) { 
        norm = Math.floor(10 / (n2.n_value[n2ptr] + 1)) 
        if (norm !== 1) {
          Libbcmath._one_mult(num1, 0, len1 + scale1 + extra + 1, norm, num1, 0)
          Libbcmath._one_mult(n2.n_value, n2ptr, len2, norm, n2.n_value, n2ptr)
        }
        qdig = 0
        if (len2 > len1) {
          qptr = len2 - len1 
        } else {
          qptr = 0 
        }
        while (qdig <= len1 + scale - len2) { 
          if (n2.n_value[n2ptr] === num1[qdig]) {
            qguess = 9
          } else {
            qguess = Math.floor((num1[qdig] * 10 + num1[qdig + 1]) / n2.n_value[n2ptr])
          }
          if (n2.n_value[n2ptr + 1] * qguess >
            (num1[qdig] * 10 + num1[qdig + 1] - n2.n_value[n2ptr] * qguess) *
            10 + num1[qdig + 2]) {
            qguess--
            if (n2.n_value[n2ptr + 1] * qguess >
              (num1[qdig] * 10 + num1[qdig + 1] - n2.n_value[n2ptr] * qguess) *
              10 + num1[qdig + 2]) {
              qguess--
            }
          }
          borrow = 0
          if (qguess !== 0) {
            mval[0] = 0 
            Libbcmath._one_mult(n2.n_value, n2ptr, len2, qguess, mval, 1)
            ptr1 = qdig + len2 
            ptr2 = len2 
            for (count = 0; count < len2 + 1; count++) {
              if (ptr2 < 0) {
                val = num1[ptr1] - 0 - borrow 
              } else {
                val = num1[ptr1] - mval[ptr2--] - borrow
              }
              if (val < 0) {
                val += 10
                borrow = 1
              } else {
                borrow = 0
              }
              num1[ptr1--] = val
            }
          }
          if (borrow === 1) {
            qguess--
            ptr1 = qdig + len2 
            ptr2 = len2 - 1 
            carry = 0
            for (count = 0; count < len2; count++) {
              if (ptr2 < 0) {
                val = num1[ptr1] + 0 + carry
              } else {
                val = num1[ptr1] + n2.n_value[ptr2--] + carry
              }
              if (val > 9) {
                val -= 10
                carry = 1
              } else {
                carry = 0
              }
              num1[ptr1--] = val 
            }
            if (carry === 1) {
              num1[ptr1] = (num1[ptr1] + 1) % 10
            }
          }
          qval.n_value[qptr++] = qguess 
          qdig++
        }
      }
      qval.n_sign = (n1.n_sign === n2.n_sign ? Libbcmath.PLUS : Libbcmath.MINUS)
      if (Libbcmath.bc_is_zero(qval)) {
        qval.n_sign = Libbcmath.PLUS
      }
      Libbcmath._bc_rm_leading_zeros(qval)
      return qval
    },
    MUL_BASE_DIGITS: 80,
    MUL_SMALL_DIGITS: (80 / 4),
    bc_multiply: function (n1, n2, scale) {
      var pval 
      var len1, len2 
      var fullScale, prodScale 
      len1 = n1.n_len + n1.n_scale
      len2 = n2.n_len + n2.n_scale
      fullScale = n1.n_scale + n2.n_scale
      prodScale = Libbcmath.MIN(
        fullScale, Libbcmath.MAX(scale, Libbcmath.MAX(n1.n_scale, n2.n_scale))
      )
      pval = Libbcmath._bc_rec_mul(n1, len1, n2, len2, fullScale)
      pval.n_sign = (n1.n_sign === n2.n_sign ? Libbcmath.PLUS : Libbcmath.MINUS)
      pval.n_len = len2 + len1 + 1 - fullScale
      pval.n_scale = prodScale
      Libbcmath._bc_rm_leading_zeros(pval)
      if (Libbcmath.bc_is_zero(pval)) {
        pval.n_sign = Libbcmath.PLUS
      }
      return pval
    },
    new_sub_num: function (length, scale, value, ptr = 0) {
      var temp = new Libbcmath.bc_num() 
      temp.n_sign = Libbcmath.PLUS
      temp.n_len = length
      temp.n_scale = scale
      temp.n_value = Libbcmath.safe_emalloc(1, length + scale, 0)
      Libbcmath.memcpy(temp.n_value, 0, value, ptr, length + scale)
      return temp
    },
    _bc_simp_mul: function (n1, n1len, n2, n2len, fullScale) {
      var prod 
      var n1ptr, n2ptr, pvptr 
      var n1end, n2end 
      var indx, sum, prodlen 
      prodlen = n1len + n2len + 1
      prod = Libbcmath.bc_new_num(prodlen, 0)
      n1end = n1len - 1 
      n2end = n2len - 1 
      pvptr = prodlen - 1 
      sum = 0
      for (indx = 0; indx < prodlen - 1; indx++) {
        n1ptr = n1end - Libbcmath.MAX(0, indx - n2len + 1)
        n2ptr = n2end - Libbcmath.MIN(indx, n2len - 1)
        while ((n1ptr >= 0) && (n2ptr <= n2end)) {
          sum += n1.n_value[n1ptr--] * n2.n_value[n2ptr++]
        }
        prod.n_value[pvptr--] = Math.floor(sum % Libbcmath.BASE)
        sum = Math.floor(sum / Libbcmath.BASE) 
      }
      prod.n_value[pvptr] = sum 
      return prod
    },
    _bc_shift_addsub: function (accum, val, shift, sub) {
      var accp, valp 
      var count, carry 
      count = val.n_len
      if (val.n_value[0] === 0) {
        count--
      }
      if (accum.n_len + accum.n_scale < shift + count) {
        throw new Error('len + scale < shift + count') 
      }
      accp = accum.n_len + accum.n_scale - shift - 1
      valp = val.n_len - 1 
      carry = 0
      if (sub) {
        while (count--) {
          accum.n_value[accp] -= val.n_value[valp--] + carry 
          if (accum.n_value[accp] < 0) { 
            carry = 1
            accum.n_value[accp--] += Libbcmath.BASE 
          } else {
            carry = 0
            accp--
          }
        }
        while (carry) {
          accum.n_value[accp] -= carry 
          if (accum.n_value[accp] < 0) { 
            accum.n_value[accp--] += Libbcmath.BASE 
          } else {
            carry = 0
          }
        }
      } else {
        while (count--) {
          accum.n_value[accp] += val.n_value[valp--] + carry 
          if (accum.n_value[accp] > (Libbcmath.BASE - 1)) { 
            carry = 1
            accum.n_value[accp--] -= Libbcmath.BASE 
          } else {
            carry = 0
            accp--
          }
        }
        while (carry) {
          accum.n_value[accp] += carry 
          if (accum.n_value[accp] > (Libbcmath.BASE - 1)) { 
            accum.n_value[accp--] -= Libbcmath.BASE 
          } else {
            carry = 0
          }
        }
      }
      return true 
    },
    _bc_rec_mul: function (u, ulen, v, vlen, fullScale) {
      var prod 
      var u0, u1, v0, v1 
      var m1, m2, m3, d1, d2 
      var n, prodlen, m1zero 
      var d1len, d2len 
      if ((ulen + vlen) < Libbcmath.MUL_BASE_DIGITS ||
        ulen < Libbcmath.MUL_SMALL_DIGITS ||
        vlen < Libbcmath.MUL_SMALL_DIGITS) {
        return Libbcmath._bc_simp_mul(u, ulen, v, vlen, fullScale)
      }
      n = Math.floor((Libbcmath.MAX(ulen, vlen) + 1) / 2)
      if (ulen < n) {
        u1 = Libbcmath.bc_init_num() 
        u0 = Libbcmath.new_sub_num(ulen, 0, u.n_value)
      } else {
        u1 = Libbcmath.new_sub_num(ulen - n, 0, u.n_value)
        u0 = Libbcmath.new_sub_num(n, 0, u.n_value, ulen - n)
      }
      if (vlen < n) {
        v1 = Libbcmath.bc_init_num() 
        v0 = Libbcmath.new_sub_num(vlen, 0, v.n_value)
      } else {
        v1 = Libbcmath.new_sub_num(vlen - n, 0, v.n_value)
        v0 = Libbcmath.new_sub_num(n, 0, v.n_value, vlen - n)
      }
      Libbcmath._bc_rm_leading_zeros(u1)
      Libbcmath._bc_rm_leading_zeros(u0)
      Libbcmath._bc_rm_leading_zeros(v1)
      Libbcmath._bc_rm_leading_zeros(v0)
      m1zero = Libbcmath.bc_is_zero(u1) || Libbcmath.bc_is_zero(v1)
      d1 = Libbcmath.bc_init_num() 
      d2 = Libbcmath.bc_init_num() 
      d1 = Libbcmath.bc_sub(u1, u0, 0)
      d1len = d1.n_len
      d2 = Libbcmath.bc_sub(v0, v1, 0)
      d2len = d2.n_len
      if (m1zero) {
        m1 = Libbcmath.bc_init_num() 
      } else {
        m1 = Libbcmath._bc_rec_mul(u1, u1.n_len, v1, v1.n_len, 0)
      }
      if (Libbcmath.bc_is_zero(d1) || Libbcmath.bc_is_zero(d2)) {
        m2 = Libbcmath.bc_init_num() 
      } else {
        m2 = Libbcmath._bc_rec_mul(d1, d1len, d2, d2len, 0)
      }
      if (Libbcmath.bc_is_zero(u0) || Libbcmath.bc_is_zero(v0)) {
        m3 = Libbcmath.bc_init_num() 
      } else {
        m3 = Libbcmath._bc_rec_mul(u0, u0.n_len, v0, v0.n_len, 0)
      }
      prodlen = ulen + vlen + 1
      prod = Libbcmath.bc_new_num(prodlen, 0)
      if (!m1zero) {
        Libbcmath._bc_shift_addsub(prod, m1, 2 * n, 0)
        Libbcmath._bc_shift_addsub(prod, m1, n, 0)
      }
      Libbcmath._bc_shift_addsub(prod, m3, n, 0)
      Libbcmath._bc_shift_addsub(prod, m3, 0, 0)
      Libbcmath._bc_shift_addsub(prod, m2, n, d1.n_sign !== d2.n_sign)
      return prod
    },
    _bc_do_compare: function (n1, n2, useSign, ignoreLast) {
      var n1ptr, n2ptr 
      var count 
      if (useSign && (n1.n_sign !== n2.n_sign)) {
        if (n1.n_sign === Libbcmath.PLUS) {
          return (1) 
        } else {
          return (-1) 
        }
      }
      if (n1.n_len !== n2.n_len) {
        if (n1.n_len > n2.n_len) { 
          if (!useSign || (n1.n_sign === Libbcmath.PLUS)) {
            return (1)
          } else {
            return (-1)
          }
        } else { 
          if (!useSign || (n1.n_sign === Libbcmath.PLUS)) {
            return (-1)
          } else {
            return (1)
          }
        }
      }
      count = n1.n_len + Math.min(n1.n_scale, n2.n_scale)
      n1ptr = 0
      n2ptr = 0
      while ((count > 0) && (n1.n_value[n1ptr] === n2.n_value[n2ptr])) {
        n1ptr++
        n2ptr++
        count--
      }
      if (ignoreLast && (count === 1) && (n1.n_scale === n2.n_scale)) {
        return (0)
      }
      if (count !== 0) {
        if (n1.n_value[n1ptr] > n2.n_value[n2ptr]) { 
          if (!useSign || n1.n_sign === Libbcmath.PLUS) {
            return (1)
          } else {
            return (-1)
          }
        } else { 
          if (!useSign || n1.n_sign === Libbcmath.PLUS) {
            return (-1)
          } else {
            return (1)
          }
        }
      }
      if (n1.n_scale !== n2.n_scale) {
        if (n1.n_scale > n2.n_scale) {
          for (count = (n1.n_scale - n2.n_scale); count > 0; count--) {
            if (n1.n_value[n1ptr++] !== 0) { 
              if (!useSign || n1.n_sign === Libbcmath.PLUS) {
                return (1)
              } else {
                return (-1)
              }
            }
          }
        } else {
          for (count = (n2.n_scale - n1.n_scale); count > 0; count--) {
            if (n2.n_value[n2ptr++] !== 0) { 
              if (!useSign || n1.n_sign === Libbcmath.PLUS) {
                return (-1)
              } else {
                return (1)
              }
            }
          }
        }
      }
      return (0)
    },
    bc_sub: function (n1, n2, scaleMin) {
      var diff 
      var cmpRes, resScale 
      if (n1.n_sign !== n2.n_sign) {
        diff = Libbcmath._bc_do_add(n1, n2, scaleMin)
        diff.n_sign = n1.n_sign
      } else { 
        cmpRes = Libbcmath._bc_do_compare(n1, n2, false, false)
        switch (cmpRes) {
          case -1:
            diff = Libbcmath._bc_do_sub(n2, n1, scaleMin)
            diff.n_sign = (n2.n_sign === Libbcmath.PLUS ? Libbcmath.MINUS : Libbcmath.PLUS)
            break
          case 0:
            resScale = Libbcmath.MAX(scaleMin, Libbcmath.MAX(n1.n_scale, n2.n_scale))
            diff = Libbcmath.bc_new_num(1, resScale)
            Libbcmath.memset(diff.n_value, 0, 0, resScale + 1)
            break
          case 1:
            diff = Libbcmath._bc_do_sub(n1, n2, scaleMin)
            diff.n_sign = n1.n_sign
            break
        }
      }
      return diff
    },
    _bc_do_add: function (n1, n2, scaleMin) {
      var sum 
      var sumScale, sumDigits 
      var n1ptr, n2ptr, sumptr 
      var carry, n1bytes, n2bytes 
      var tmp 
      sumScale = Libbcmath.MAX(n1.n_scale, n2.n_scale)
      sumDigits = Libbcmath.MAX(n1.n_len, n2.n_len) + 1
      sum = Libbcmath.bc_new_num(sumDigits, Libbcmath.MAX(sumScale, scaleMin))
      n1bytes = n1.n_scale
      n2bytes = n2.n_scale
      n1ptr = (n1.n_len + n1bytes - 1)
      n2ptr = (n2.n_len + n2bytes - 1)
      sumptr = (sumScale + sumDigits - 1)
      if (n1bytes !== n2bytes) {
        if (n1bytes > n2bytes) {
          while (n1bytes > n2bytes) {
            sum.n_value[sumptr--] = n1.n_value[n1ptr--]
            n1bytes--
          }
        } else {
          while (n2bytes > n1bytes) {
            sum.n_value[sumptr--] = n2.n_value[n2ptr--]
            n2bytes--
          }
        }
      }
      n1bytes += n1.n_len
      n2bytes += n2.n_len
      carry = 0
      while ((n1bytes > 0) && (n2bytes > 0)) {
        tmp = n1.n_value[n1ptr--] + n2.n_value[n2ptr--] + carry
        if (tmp >= Libbcmath.BASE) {
          carry = 1
          tmp -= Libbcmath.BASE 
        } else {
          carry = 0
        }
        sum.n_value[sumptr] = tmp
        sumptr--
        n1bytes--
        n2bytes--
      }
      if (n1bytes === 0) {
        while (n2bytes-- > 0) {
          tmp = n2.n_value[n2ptr--] + carry
          if (tmp >= Libbcmath.BASE) {
            carry = 1
            tmp -= Libbcmath.BASE
          } else {
            carry = 0
          }
          sum.n_value[sumptr--] = tmp
        }
      } else {
        while (n1bytes-- > 0) {
          tmp = n1.n_value[n1ptr--] + carry
          if (tmp >= Libbcmath.BASE) {
            carry = 1
            tmp -= Libbcmath.BASE
          } else {
            carry = 0
          }
          sum.n_value[sumptr--] = tmp
        }
      }
      if (carry === 1) {
        sum.n_value[sumptr] += 1
      }
      Libbcmath._bc_rm_leading_zeros(sum)
      return sum
    },
    _bc_do_sub: function (n1, n2, scaleMin) {
      var diff 
      var diffScale, diffLen 
      var minScale, minLen 
      var n1ptr, n2ptr, diffptr 
      var borrow, count, val 
      diffLen = Libbcmath.MAX(n1.n_len, n2.n_len)
      diffScale = Libbcmath.MAX(n1.n_scale, n2.n_scale)
      minLen = Libbcmath.MIN(n1.n_len, n2.n_len)
      minScale = Libbcmath.MIN(n1.n_scale, n2.n_scale)
      diff = Libbcmath.bc_new_num(diffLen, Libbcmath.MAX(diffScale, scaleMin))
      n1ptr = (n1.n_len + n1.n_scale - 1)
      n2ptr = (n2.n_len + n2.n_scale - 1)
      diffptr = (diffLen + diffScale - 1)
      borrow = 0
      if (n1.n_scale !== minScale) {
        for (count = n1.n_scale - minScale; count > 0; count--) {
          diff.n_value[diffptr--] = n1.n_value[n1ptr--]
        }
      } else {
        for (count = n2.n_scale - minScale; count > 0; count--) {
          val = 0 - n2.n_value[n2ptr--] - borrow
          if (val < 0) {
            val += Libbcmath.BASE
            borrow = 1
          } else {
            borrow = 0
          }
          diff.n_value[diffptr--] = val
        }
      }
      for (count = 0; count < minLen + minScale; count++) {
        val = n1.n_value[n1ptr--] - n2.n_value[n2ptr--] - borrow
        if (val < 0) {
          val += Libbcmath.BASE
          borrow = 1
        } else {
          borrow = 0
        }
        diff.n_value[diffptr--] = val
      }
      if (diffLen !== minLen) {
        for (count = diffLen - minLen; count > 0; count--) {
          val = n1.n_value[n1ptr--] - borrow
          if (val < 0) {
            val += Libbcmath.BASE
            borrow = 1
          } else {
            borrow = 0
          }
          diff.n_value[diffptr--] = val
        }
      }
      Libbcmath._bc_rm_leading_zeros(diff)
      return diff
    },
    bc_new_num: function (length, scale) {
      var temp 
      temp = new Libbcmath.bc_num() 
      temp.n_sign = Libbcmath.PLUS
      temp.n_len = length
      temp.n_scale = scale
      temp.n_value = Libbcmath.safe_emalloc(1, length + scale, 0)
      Libbcmath.memset(temp.n_value, 0, 0, length + scale)
      return temp
    },
    safe_emalloc: function (size, len, extra) {
      return Array((size * len) + extra)
    },
    bc_init_num: function () {
      return new Libbcmath.bc_new_num(1, 0) 
    },
    _bc_rm_leading_zeros: function (num) {
      while ((num.n_value[0] === 0) && (num.n_len > 1)) {
        num.n_value.shift()
        num.n_len--
      }
    },
    php_str2num: function (str) {
      var p
      p = str.indexOf('.')
      if (p === -1) {
        return Libbcmath.bc_str2num(str, 0)
      } else {
        return Libbcmath.bc_str2num(str, (str.length - p))
      }
    },
    CH_VAL: function (c) {
      return c - '0' 
    },
    BCD_CHAR: function (d) {
      return d + '0' 
    },
    isdigit: function (c) {
      return isNaN(parseInt(c, 10))
    },
    bc_str2num: function (strIn, scale) {
      var str, num, ptr, digits, strscale, zeroInt, nptr
      str = strIn.split('') 
      ptr = 0 
      digits = 0
      strscale = 0
      zeroInt = false
      if ((str[ptr] === '+') || (str[ptr] === '-')) {
        ptr++ 
      }
      while (str[ptr] === '0') {
        ptr++ 
      }
      while ((str[ptr]) % 1 === 0) { 
        ptr++
        digits++ 
      }
      if (str[ptr] === '.') {
        ptr++ 
      }
      while ((str[ptr]) % 1 === 0) { 
        ptr++
        strscale++ 
      }
      if ((str[ptr]) || (digits + strscale === 0)) {
        return Libbcmath.bc_init_num()
      }
      strscale = Libbcmath.MIN(strscale, scale)
      if (digits === 0) {
        zeroInt = true
        digits = 1
      }
      num = Libbcmath.bc_new_num(digits, strscale)
      ptr = 0 
      if (str[ptr] === '-') {
        num.n_sign = Libbcmath.MINUS
        ptr++
      } else {
        num.n_sign = Libbcmath.PLUS
        if (str[ptr] === '+') {
          ptr++
        }
      }
      while (str[ptr] === '0') {
        ptr++ 
      }
      nptr = 0 
      if (zeroInt) {
        num.n_value[nptr++] = 0
        digits = 0
      }
      for (; digits > 0; digits--) {
        num.n_value[nptr++] = Libbcmath.CH_VAL(str[ptr++])
      }
      if (strscale > 0) {
        ptr++ 
        for (; strscale > 0; strscale--) {
          num.n_value[nptr++] = Libbcmath.CH_VAL(str[ptr++])
        }
      }
      return num
    },
    cint: function (v) {
      if (typeof v === 'undefined') {
        v = 0
      }
      var x = parseInt(v, 10)
      if (isNaN(x)) {
        x = 0
      }
      return x
    },
    MIN: function (a, b) {
      return ((a > b) ? b : a)
    },
    MAX: function (a, b) {
      return ((a > b) ? a : b)
    },
    ODD: function (a) {
      return (a & 1)
    },
    memset: function (r, ptr, chr, len) {
      var i
      for (i = 0; i < len; i++) {
        r[ptr + i] = chr
      }
    },
    memcpy: function (dest, ptr, src, srcptr, len) {
      var i
      for (i = 0; i < len; i++) {
        dest[ptr + i] = src[srcptr + i]
      }
      return true
    },
    bc_is_zero: function (num) {
      var count 
      var nptr 
      count = num.n_len + num.n_scale
      nptr = 0 
      while ((count > 0) && (num.n_value[nptr++] === 0)) {
        count--
      }
      if (count !== 0) {
        return false
      } else {
        return true
      }
    },
    bc_out_of_memory: function () {
      throw new Error('(BC) Out of memory')
    }
  }
  return Libbcmath
}
