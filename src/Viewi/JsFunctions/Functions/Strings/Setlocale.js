function setlocale (category, locale) {
  var getenv = window.getenv
  var categ = ''
  var cats = []
  var i = 0
  var _copy = function _copy (orig) {
    if (orig instanceof RegExp) {
      return new RegExp(orig)
    } else if (orig instanceof Date) {
      return new Date(orig)
    }
    var newObj = {}
    for (var i in orig) {
      if (typeof orig[i] === 'object') {
        newObj[i] = _copy(orig[i])
      } else {
        newObj[i] = orig[i]
      }
    }
    return newObj
  }
  var _nplurals2a = function (n) {
    return n !== 1 ? 1 : 0
  }
  var _nplurals2b = function (n) {
    return n > 1 ? 1 : 0
  }
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  if (!$locutus.php.locales ||
    !$locutus.php.locales.fr_CA ||
    !$locutus.php.locales.fr_CA.LC_TIME ||
    !$locutus.php.locales.fr_CA.LC_TIME.x) {
    $locutus.php.locales = {}
    $locutus.php.locales.en = {
      'LC_COLLATE': function (str1, str2) {
        return (str1 === str2) ? 0 : ((str1 > str2) ? 1 : -1)
      },
      'LC_CTYPE': {
        an: /^[A-Za-z\d]+$/g,
        al: /^[A-Za-z]+$/g,
        ct: /^[\u0000-\u001F\u007F]+$/g,
        dg: /^[\d]+$/g,
        gr: /^[\u0021-\u007E]+$/g,
        lw: /^[a-z]+$/g,
        pr: /^[\u0020-\u007E]+$/g,
        pu: /^[\u0021-\u002F\u003A-\u0040\u005B-\u0060\u007B-\u007E]+$/g,
        sp: /^[\f\n\r\t\v ]+$/g,
        up: /^[A-Z]+$/g,
        xd: /^[A-Fa-f\d]+$/g,
        CODESET: 'UTF-8',
        lower: 'abcdefghijklmnopqrstuvwxyz',
        upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
      },
      'LC_TIME': {
        a: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        A: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        b: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        B: ['January', 'February', 'March', 'April', 'May', 'June', 'July',
          'August', 'September', 'October',
          'November', 'December'
        ],
        c: '%a %d %b %Y %r %Z',
        p: ['AM', 'PM'],
        P: ['am', 'pm'],
        r: '%I:%M:%S %p',
        x: '%m/%d/%Y',
        X: '%r',
        alt_digits: '',
        ERA: '',
        ERA_YEAR: '',
        ERA_D_T_FMT: '',
        ERA_D_FMT: '',
        ERA_T_FMT: ''
      },
      'LC_MONETARY': {
        int_curr_symbol: 'USD',
        currency_symbol: '$',
        mon_decimal_point: '.',
        mon_thousands_sep: ',',
        mon_grouping: [3],
        positive_sign: '',
        negative_sign: '-',
        int_frac_digits: 2,
        frac_digits: 2,
        p_cs_precedes: 1,
        p_sep_by_space: 0,
        n_cs_precedes: 1,
        n_sep_by_space: 0,
        p_sign_posn: 3,
        n_sign_posn: 0 
      },
      'LC_NUMERIC': {
        decimal_point: '.',
        thousands_sep: ',',
        grouping: [3] 
      },
      'LC_MESSAGES': {
        YESEXPR: '^[yY].*',
        NOEXPR: '^[nN].*',
        YESSTR: '',
        NOSTR: ''
      },
      nplurals: _nplurals2a
    }
    $locutus.php.locales.en_US = _copy($locutus.php.locales.en)
    $locutus.php.locales.en_US.LC_TIME.c = '%a %d %b %Y %r %Z'
    $locutus.php.locales.en_US.LC_TIME.x = '%D'
    $locutus.php.locales.en_US.LC_TIME.X = '%r'
    $locutus.php.locales.en_US.LC_MONETARY.int_curr_symbol = 'USD '
    $locutus.php.locales.en_US.LC_MONETARY.p_sign_posn = 1
    $locutus.php.locales.en_US.LC_MONETARY.n_sign_posn = 1
    $locutus.php.locales.en_US.LC_MONETARY.mon_grouping = [3, 3]
    $locutus.php.locales.en_US.LC_NUMERIC.thousands_sep = ''
    $locutus.php.locales.en_US.LC_NUMERIC.grouping = []
    $locutus.php.locales.en_GB = _copy($locutus.php.locales.en)
    $locutus.php.locales.en_GB.LC_TIME.r = '%l:%M:%S %P %Z'
    $locutus.php.locales.en_AU = _copy($locutus.php.locales.en_GB)
    $locutus.php.locales.C = _copy($locutus.php.locales.en)
    $locutus.php.locales.C.LC_CTYPE.CODESET = 'ANSI_X3.4-1968'
    $locutus.php.locales.C.LC_MONETARY = {
      int_curr_symbol: '',
      currency_symbol: '',
      mon_decimal_point: '',
      mon_thousands_sep: '',
      mon_grouping: [],
      p_cs_precedes: 127,
      p_sep_by_space: 127,
      n_cs_precedes: 127,
      n_sep_by_space: 127,
      p_sign_posn: 127,
      n_sign_posn: 127,
      positive_sign: '',
      negative_sign: '',
      int_frac_digits: 127,
      frac_digits: 127
    }
    $locutus.php.locales.C.LC_NUMERIC = {
      decimal_point: '.',
      thousands_sep: '',
      grouping: []
    }
    $locutus.php.locales.C.LC_TIME.c = '%a %b %e %H:%M:%S %Y'
    $locutus.php.locales.C.LC_TIME.x = '%m/%d/%y'
    $locutus.php.locales.C.LC_TIME.X = '%H:%M:%S'
    $locutus.php.locales.C.LC_MESSAGES.YESEXPR = '^[yY]'
    $locutus.php.locales.C.LC_MESSAGES.NOEXPR = '^[nN]'
    $locutus.php.locales.fr = _copy($locutus.php.locales.en)
    $locutus.php.locales.fr.nplurals = _nplurals2b
    $locutus.php.locales.fr.LC_TIME.a = ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam']
    $locutus.php.locales.fr.LC_TIME.A = ['dimanche', 'lundi', 'mardi', 'mercredi',
      'jeudi', 'vendredi', 'samedi']
    $locutus.php.locales.fr.LC_TIME.b = ['jan', 'f\u00E9v', 'mar', 'avr', 'mai',
      'jun', 'jui', 'ao\u00FB', 'sep', 'oct',
      'nov', 'd\u00E9c'
    ]
    $locutus.php.locales.fr.LC_TIME.B = ['janvier', 'f\u00E9vrier', 'mars',
      'avril', 'mai', 'juin', 'juillet', 'ao\u00FBt',
      'septembre', 'octobre', 'novembre', 'd\u00E9cembre'
    ]
    $locutus.php.locales.fr.LC_TIME.c = '%a %d %b %Y %T %Z'
    $locutus.php.locales.fr.LC_TIME.p = ['', '']
    $locutus.php.locales.fr.LC_TIME.P = ['', '']
    $locutus.php.locales.fr.LC_TIME.x = '%d.%m.%Y'
    $locutus.php.locales.fr.LC_TIME.X = '%T'
    $locutus.php.locales.fr_CA = _copy($locutus.php.locales.fr)
    $locutus.php.locales.fr_CA.LC_TIME.x = '%Y-%m-%d'
  }
  if (!$locutus.php.locale) {
    $locutus.php.locale = 'en_US'
    if (typeof window !== 'undefined' && window.document) {
      var d = window.document
      var NS_XHTML = 'https://www.w3.org/1999/xhtml'
      var NS_XML = 'https://www.w3.org/XML/1998/namespace'
      if (d.getElementsByTagNameNS &&
        d.getElementsByTagNameNS(NS_XHTML, 'html')[0]) {
        if (d.getElementsByTagNameNS(NS_XHTML, 'html')[0].getAttributeNS &&
          d.getElementsByTagNameNS(NS_XHTML, 'html')[0].getAttributeNS(NS_XML, 'lang')) {
          $locutus.php.locale = d.getElementsByTagName(NS_XHTML, 'html')[0]
            .getAttributeNS(NS_XML, 'lang')
        } else if (d.getElementsByTagNameNS(NS_XHTML, 'html')[0].lang) {
          $locutus.php.locale = d.getElementsByTagNameNS(NS_XHTML, 'html')[0].lang
        }
      } else if (d.getElementsByTagName('html')[0] &&
        d.getElementsByTagName('html')[0].lang) {
        $locutus.php.locale = d.getElementsByTagName('html')[0].lang
      }
    }
  }
  $locutus.php.locale = $locutus.php.locale.replace('-', '_')
  if (!($locutus.php.locale in $locutus.php.locales)) {
    if ($locutus.php.locale.replace(/_[a-zA-Z]+$/, '') in $locutus.php.locales) {
      $locutus.php.locale = $locutus.php.locale.replace(/_[a-zA-Z]+$/, '')
    }
  }
  if (!$locutus.php.localeCategories) {
    $locutus.php.localeCategories = {
      'LC_COLLATE': $locutus.php.locale,
      'LC_CTYPE': $locutus.php.locale,
      'LC_MONETARY': $locutus.php.locale,
      'LC_NUMERIC': $locutus.php.locale,
      'LC_TIME': $locutus.php.locale,
      'LC_MESSAGES': $locutus.php.locale
    }
  }
  if (locale === null || locale === '') {
    locale = getenv(category) || getenv('LANG')
  } else if (Object.prototype.toString.call(locale) === '[object Array]') {
    for (i = 0; i < locale.length; i++) {
      if (!(locale[i] in $locutus.php.locales)) {
        if (i === locale.length - 1) {
          return false
        }
        continue
      }
      locale = locale[i]
      break
    }
  }
  if (locale === '0' || locale === 0) {
    if (category === 'LC_ALL') {
      for (categ in $locutus.php.localeCategories) {
        cats.push(categ + '=' + $locutus.php.localeCategories[categ])
      }
      return cats.join(';')
    }
    return $locutus.php.localeCategories[category]
  }
  if (!(locale in $locutus.php.locales)) {
    return false
  }
  if (category === 'LC_ALL') {
    for (categ in $locutus.php.localeCategories) {
      $locutus.php.localeCategories[categ] = locale
    }
  } else {
    $locutus.php.localeCategories[category] = locale
  }
  return locale
}
