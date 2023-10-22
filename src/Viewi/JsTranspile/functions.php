<?php

use Viewi\PhpJsFunctions\Helpers\Bc;
use Viewi\PhpJsFunctions\Helpers\PhpCastString;
use Viewi\PhpJsFunctions\Helpers\PhpCastFloat;
use Viewi\PhpJsFunctions\Helpers\PhpCastInt;
use Viewi\PhpJsFunctions\Array\ArrayChangeKeyCase;
use Viewi\PhpJsFunctions\Array\ArrayChunk;
use Viewi\PhpJsFunctions\Array\ArrayColumn;
use Viewi\PhpJsFunctions\Array\ArrayCombine;
use Viewi\PhpJsFunctions\Array\ArrayCountValues;
use Viewi\PhpJsFunctions\Array\ArrayDiff;
use Viewi\PhpJsFunctions\Array\ArrayDiffAssoc;
use Viewi\PhpJsFunctions\Array\ArrayDiffKey;
use Viewi\PhpJsFunctions\Array\ArrayDiffUassoc;
use Viewi\PhpJsFunctions\Array\ArrayDiffUkey;
use Viewi\PhpJsFunctions\Array\ArrayFill;
use Viewi\PhpJsFunctions\Array\ArrayFillKeys;
use Viewi\PhpJsFunctions\Array\ArrayFilter;
use Viewi\PhpJsFunctions\Array\ArrayFlip;
use Viewi\PhpJsFunctions\Array\ArrayIntersect;
use Viewi\PhpJsFunctions\Array\ArrayIntersectAssoc;
use Viewi\PhpJsFunctions\Array\ArrayIntersectKey;
use Viewi\PhpJsFunctions\Array\ArrayIntersectUassoc;
use Viewi\PhpJsFunctions\Array\ArrayIntersectUkey;
use Viewi\PhpJsFunctions\Array\ArrayKeyExists;
use Viewi\PhpJsFunctions\Array\ArrayKeys;
use Viewi\PhpJsFunctions\Array\ArrayMap;
use Viewi\PhpJsFunctions\Array\ArrayMerge;
use Viewi\PhpJsFunctions\Array\ArrayMergeRecursive;
use Viewi\PhpJsFunctions\Array\ArrayMultisort;
use Viewi\PhpJsFunctions\Array\ArrayPad;
use Viewi\PhpJsFunctions\Array\ArrayPop;
use Viewi\PhpJsFunctions\Array\ArrayProduct;
use Viewi\PhpJsFunctions\Array\ArrayPush;
use Viewi\PhpJsFunctions\Array\ArrayRand;
use Viewi\PhpJsFunctions\Array\ArrayReduce;
use Viewi\PhpJsFunctions\Array\ArrayReplace;
use Viewi\PhpJsFunctions\Array\ArrayReplaceRecursive;
use Viewi\PhpJsFunctions\Array\ArrayReverse;
use Viewi\PhpJsFunctions\Array\ArraySearch;
use Viewi\PhpJsFunctions\Array\ArrayShift;
use Viewi\PhpJsFunctions\Array\ArraySlice;
use Viewi\PhpJsFunctions\Array\ArraySplice;
use Viewi\PhpJsFunctions\Array\ArraySum;
use Viewi\PhpJsFunctions\Array\ArrayUdiff;
use Viewi\PhpJsFunctions\Array\ArrayUdiffAssoc;
use Viewi\PhpJsFunctions\Array\ArrayUdiffUassoc;
use Viewi\PhpJsFunctions\Array\ArrayUintersect;
use Viewi\PhpJsFunctions\Array\ArrayUintersectUassoc;
use Viewi\PhpJsFunctions\Array\ArrayUnique;
use Viewi\PhpJsFunctions\Array\ArrayUnshift;
use Viewi\PhpJsFunctions\Array\ArrayValues;
use Viewi\PhpJsFunctions\Array\ArrayWalk;
use Viewi\PhpJsFunctions\Array\ArrayWalkRecursive;
use Viewi\PhpJsFunctions\Array\Arsort;
use Viewi\PhpJsFunctions\Array\Asort;
use Viewi\PhpJsFunctions\Array\Count;
use Viewi\PhpJsFunctions\Array\Current;
use Viewi\PhpJsFunctions\Array\Each;
use Viewi\PhpJsFunctions\Array\End;
use Viewi\PhpJsFunctions\Array\InArray;
use Viewi\PhpJsFunctions\Array\Key;
use Viewi\PhpJsFunctions\Array\Krsort;
use Viewi\PhpJsFunctions\Array\Ksort;
use Viewi\PhpJsFunctions\Array\Natcasesort;
use Viewi\PhpJsFunctions\Array\Natsort;
use Viewi\PhpJsFunctions\Array\Next;
use Viewi\PhpJsFunctions\Array\Pos;
use Viewi\PhpJsFunctions\Array\Prev;
use Viewi\PhpJsFunctions\Array\Range;
use Viewi\PhpJsFunctions\Array\Reset;
use Viewi\PhpJsFunctions\Array\Rsort;
use Viewi\PhpJsFunctions\Array\Shuffle;
use Viewi\PhpJsFunctions\Array\Sizeof;
use Viewi\PhpJsFunctions\Array\Sort;
use Viewi\PhpJsFunctions\Array\Uasort;
use Viewi\PhpJsFunctions\Array\Uksort;
use Viewi\PhpJsFunctions\Array\Usort;
use Viewi\PhpJsFunctions\Bc\Bcadd;
use Viewi\PhpJsFunctions\Bc\Bccomp;
use Viewi\PhpJsFunctions\Bc\Bcdiv;
use Viewi\PhpJsFunctions\Bc\Bcmul;
use Viewi\PhpJsFunctions\Bc\Bcround;
use Viewi\PhpJsFunctions\Bc\Bcscale;
use Viewi\PhpJsFunctions\Bc\Bcsub;
use Viewi\PhpJsFunctions\Ctype\CtypeAlnum;
use Viewi\PhpJsFunctions\Ctype\CtypeAlpha;
use Viewi\PhpJsFunctions\Ctype\CtypeCntrl;
use Viewi\PhpJsFunctions\Ctype\CtypeDigit;
use Viewi\PhpJsFunctions\Ctype\CtypeGraph;
use Viewi\PhpJsFunctions\Ctype\CtypeLower;
use Viewi\PhpJsFunctions\Ctype\CtypePrint;
use Viewi\PhpJsFunctions\Ctype\CtypePunct;
use Viewi\PhpJsFunctions\Ctype\CtypeSpace;
use Viewi\PhpJsFunctions\Ctype\CtypeUpper;
use Viewi\PhpJsFunctions\Ctype\CtypeXdigit;
use Viewi\PhpJsFunctions\Datetime\Checkdate;
use Viewi\PhpJsFunctions\Datetime\Date;
use Viewi\PhpJsFunctions\Datetime\DateParse;
use Viewi\PhpJsFunctions\Datetime\Getdate;
use Viewi\PhpJsFunctions\Datetime\Gettimeofday;
use Viewi\PhpJsFunctions\Datetime\Gmdate;
use Viewi\PhpJsFunctions\Datetime\Gmmktime;
use Viewi\PhpJsFunctions\Datetime\Gmstrftime;
use Viewi\PhpJsFunctions\Datetime\Idate;
use Viewi\PhpJsFunctions\Datetime\Microtime;
use Viewi\PhpJsFunctions\Datetime\Mktime;
use Viewi\PhpJsFunctions\Datetime\Strftime;
use Viewi\PhpJsFunctions\Datetime\Strptime;
use Viewi\PhpJsFunctions\Datetime\Strtotime;
use Viewi\PhpJsFunctions\Datetime\Time;
use Viewi\PhpJsFunctions\Exec\Escapeshellarg;
use Viewi\PhpJsFunctions\Filesystem\Basename;
use Viewi\PhpJsFunctions\Filesystem\Dirname;
use Viewi\PhpJsFunctions\Filesystem\FileGetContents;
use Viewi\PhpJsFunctions\Filesystem\Pathinfo;
use Viewi\PhpJsFunctions\Filesystem\Realpath;
use Viewi\PhpJsFunctions\Funchand\CallUserFunc;
use Viewi\PhpJsFunctions\Funchand\CallUserFuncArray;
use Viewi\PhpJsFunctions\Funchand\CreateFunction;
use Viewi\PhpJsFunctions\Funchand\FunctionExists;
use Viewi\PhpJsFunctions\Funchand\GetDefinedFunctions;
use Viewi\PhpJsFunctions\I18n\I18nLocGetDefault;
use Viewi\PhpJsFunctions\I18n\I18nLocSetDefault;
use Viewi\PhpJsFunctions\Info\AssertOptions;
use Viewi\PhpJsFunctions\Info\Getenv;
use Viewi\PhpJsFunctions\Info\IniGet;
use Viewi\PhpJsFunctions\Info\IniSet;
use Viewi\PhpJsFunctions\Info\SetTimeLimit;
use Viewi\PhpJsFunctions\Info\VersionCompare;
use Viewi\PhpJsFunctions\Json\JsonDecode;
use Viewi\PhpJsFunctions\Json\JsonEncode;
use Viewi\PhpJsFunctions\Json\JsonLastError;
use Viewi\PhpJsFunctions\Math\Abs;
use Viewi\PhpJsFunctions\Math\Acos;
use Viewi\PhpJsFunctions\Math\Acosh;
use Viewi\PhpJsFunctions\Math\Asin;
use Viewi\PhpJsFunctions\Math\Asinh;
use Viewi\PhpJsFunctions\Math\Atan;
use Viewi\PhpJsFunctions\Math\Atan2;
use Viewi\PhpJsFunctions\Math\Atanh;
use Viewi\PhpJsFunctions\Math\BaseConvert;
use Viewi\PhpJsFunctions\Math\Bindec;
use Viewi\PhpJsFunctions\Math\Ceil;
use Viewi\PhpJsFunctions\Math\Cos;
use Viewi\PhpJsFunctions\Math\Cosh;
use Viewi\PhpJsFunctions\Math\Decbin;
use Viewi\PhpJsFunctions\Math\Dechex;
use Viewi\PhpJsFunctions\Math\Decoct;
use Viewi\PhpJsFunctions\Math\Deg2rad;
use Viewi\PhpJsFunctions\Math\Exp;
use Viewi\PhpJsFunctions\Math\Expm1;
use Viewi\PhpJsFunctions\Math\Floor;
use Viewi\PhpJsFunctions\Math\Fmod;
use Viewi\PhpJsFunctions\Math\Getrandmax;
use Viewi\PhpJsFunctions\Math\Hexdec;
use Viewi\PhpJsFunctions\Math\Hypot;
use Viewi\PhpJsFunctions\Math\IsFinite;
use Viewi\PhpJsFunctions\Math\IsInfinite;
use Viewi\PhpJsFunctions\Math\IsNan;
use Viewi\PhpJsFunctions\Math\LcgValue;
use Viewi\PhpJsFunctions\Math\Log;
use Viewi\PhpJsFunctions\Math\Log10;
use Viewi\PhpJsFunctions\Math\Log1p;
use Viewi\PhpJsFunctions\Math\Max;
use Viewi\PhpJsFunctions\Math\Min;
use Viewi\PhpJsFunctions\Math\MtGetrandmax;
use Viewi\PhpJsFunctions\Math\MtRand;
use Viewi\PhpJsFunctions\Math\Octdec;
use Viewi\PhpJsFunctions\Math\Pi;
use Viewi\PhpJsFunctions\Math\Pow;
use Viewi\PhpJsFunctions\Math\Rad2deg;
use Viewi\PhpJsFunctions\Math\Rand;
use Viewi\PhpJsFunctions\Math\Round;
use Viewi\PhpJsFunctions\Math\Sin;
use Viewi\PhpJsFunctions\Math\Sinh;
use Viewi\PhpJsFunctions\Math\Sqrt;
use Viewi\PhpJsFunctions\Math\Tan;
use Viewi\PhpJsFunctions\Math\Tanh;
use Viewi\PhpJsFunctions\Misc\Pack;
use Viewi\PhpJsFunctions\Misc\Uniqid;
use Viewi\PhpJsFunctions\Netgopher\GopherParsedir;
use Viewi\PhpJsFunctions\Network\InetNtop;
use Viewi\PhpJsFunctions\Network\InetPton;
use Viewi\PhpJsFunctions\Network\Ip2long;
use Viewi\PhpJsFunctions\Network\Long2ip;
use Viewi\PhpJsFunctions\Network\Setcookie;
use Viewi\PhpJsFunctions\Network\Setrawcookie;
use Viewi\PhpJsFunctions\Pcre\PregMatch;
use Viewi\PhpJsFunctions\Pcre\PregQuote;
use Viewi\PhpJsFunctions\Pcre\PregReplace;
use Viewi\PhpJsFunctions\Pcre\SqlRegcase;
use Viewi\PhpJsFunctions\Strings\Addcslashes;
use Viewi\PhpJsFunctions\Strings\Addslashes;
use Viewi\PhpJsFunctions\Strings\Bin2hex;
use Viewi\PhpJsFunctions\Strings\Chop;
use Viewi\PhpJsFunctions\Strings\Chr;
use Viewi\PhpJsFunctions\Strings\ChunkSplit;
use Viewi\PhpJsFunctions\Strings\ConvertCyrString;
use Viewi\PhpJsFunctions\Strings\ConvertUuencode;
use Viewi\PhpJsFunctions\Strings\CountChars;
use Viewi\PhpJsFunctions\Strings\Crc32;
use Viewi\PhpJsFunctions\Strings\_Echo;
use Viewi\PhpJsFunctions\Strings\Explode;
use Viewi\PhpJsFunctions\Strings\GetHtmlTranslationTable;
use Viewi\PhpJsFunctions\Strings\Hex2bin;
use Viewi\PhpJsFunctions\Strings\HtmlEntityDecode;
use Viewi\PhpJsFunctions\Strings\Htmlentities;
use Viewi\PhpJsFunctions\Strings\Htmlspecialchars;
use Viewi\PhpJsFunctions\Strings\HtmlspecialcharsDecode;
use Viewi\PhpJsFunctions\Strings\Implode;
use Viewi\PhpJsFunctions\Strings\Join;
use Viewi\PhpJsFunctions\Strings\Lcfirst;
use Viewi\PhpJsFunctions\Strings\Levenshtein;
use Viewi\PhpJsFunctions\Strings\Localeconv;
use Viewi\PhpJsFunctions\Strings\Ltrim;
use Viewi\PhpJsFunctions\Strings\Md5;
use Viewi\PhpJsFunctions\Strings\Md5File;
use Viewi\PhpJsFunctions\Strings\Metaphone;
use Viewi\PhpJsFunctions\Strings\MoneyFormat;
use Viewi\PhpJsFunctions\Strings\Nl2br;
use Viewi\PhpJsFunctions\Strings\NlLanginfo;
use Viewi\PhpJsFunctions\Strings\NumberFormat;
use Viewi\PhpJsFunctions\Strings\Ord;
use Viewi\PhpJsFunctions\Strings\ParseStr;
use Viewi\PhpJsFunctions\Strings\Printf;
use Viewi\PhpJsFunctions\Strings\QuotedPrintableDecode;
use Viewi\PhpJsFunctions\Strings\QuotedPrintableEncode;
use Viewi\PhpJsFunctions\Strings\Quotemeta;
use Viewi\PhpJsFunctions\Strings\Rtrim;
use Viewi\PhpJsFunctions\Strings\Setlocale;
use Viewi\PhpJsFunctions\Strings\Sha1;
use Viewi\PhpJsFunctions\Strings\Sha1File;
use Viewi\PhpJsFunctions\Strings\SimilarText;
use Viewi\PhpJsFunctions\Strings\Soundex;
use Viewi\PhpJsFunctions\Strings\Split;
use Viewi\PhpJsFunctions\Strings\Sprintf;
use Viewi\PhpJsFunctions\Strings\Sscanf;
use Viewi\PhpJsFunctions\Strings\StrGetcsv;
use Viewi\PhpJsFunctions\Strings\StrIreplace;
use Viewi\PhpJsFunctions\Strings\StrPad;
use Viewi\PhpJsFunctions\Strings\StrRepeat;
use Viewi\PhpJsFunctions\Strings\StrReplace;
use Viewi\PhpJsFunctions\Strings\StrRot13;
use Viewi\PhpJsFunctions\Strings\StrShuffle;
use Viewi\PhpJsFunctions\Strings\StrSplit;
use Viewi\PhpJsFunctions\Strings\StrWordCount;
use Viewi\PhpJsFunctions\Strings\Strcasecmp;
use Viewi\PhpJsFunctions\Strings\Strchr;
use Viewi\PhpJsFunctions\Strings\Strcmp;
use Viewi\PhpJsFunctions\Strings\Strcoll;
use Viewi\PhpJsFunctions\Strings\Strcspn;
use Viewi\PhpJsFunctions\Strings\StripTags;
use Viewi\PhpJsFunctions\Strings\Stripos;
use Viewi\PhpJsFunctions\Strings\Stripslashes;
use Viewi\PhpJsFunctions\Strings\Stristr;
use Viewi\PhpJsFunctions\Strings\Strlen;
use Viewi\PhpJsFunctions\Strings\Strnatcasecmp;
use Viewi\PhpJsFunctions\Strings\Strnatcmp;
use Viewi\PhpJsFunctions\Strings\Strncasecmp;
use Viewi\PhpJsFunctions\Strings\Strncmp;
use Viewi\PhpJsFunctions\Strings\Strpbrk;
use Viewi\PhpJsFunctions\Strings\Strpos;
use Viewi\PhpJsFunctions\Strings\Strrchr;
use Viewi\PhpJsFunctions\Strings\Strrev;
use Viewi\PhpJsFunctions\Strings\Strripos;
use Viewi\PhpJsFunctions\Strings\Strrpos;
use Viewi\PhpJsFunctions\Strings\Strspn;
use Viewi\PhpJsFunctions\Strings\Strstr;
use Viewi\PhpJsFunctions\Strings\Strtok;
use Viewi\PhpJsFunctions\Strings\Strtolower;
use Viewi\PhpJsFunctions\Strings\Strtoupper;
use Viewi\PhpJsFunctions\Strings\Strtr;
use Viewi\PhpJsFunctions\Strings\Substr;
use Viewi\PhpJsFunctions\Strings\SubstrCompare;
use Viewi\PhpJsFunctions\Strings\SubstrCount;
use Viewi\PhpJsFunctions\Strings\SubstrReplace;
use Viewi\PhpJsFunctions\Strings\Trim;
use Viewi\PhpJsFunctions\Strings\Ucfirst;
use Viewi\PhpJsFunctions\Strings\Ucwords;
use Viewi\PhpJsFunctions\Strings\Vprintf;
use Viewi\PhpJsFunctions\Strings\Vsprintf;
use Viewi\PhpJsFunctions\Strings\Wordwrap;
use Viewi\PhpJsFunctions\Url\Base64Decode;
use Viewi\PhpJsFunctions\Url\Base64Encode;
use Viewi\PhpJsFunctions\Url\HttpBuildQuery;
use Viewi\PhpJsFunctions\Url\ParseUrl;
use Viewi\PhpJsFunctions\Url\Rawurldecode;
use Viewi\PhpJsFunctions\Url\Rawurlencode;
use Viewi\PhpJsFunctions\Url\Urldecode;
use Viewi\PhpJsFunctions\Url\Urlencode;
use Viewi\PhpJsFunctions\Var\Boolval;
use Viewi\PhpJsFunctions\Var\Doubleval;
use Viewi\PhpJsFunctions\Var\_Empty;
use Viewi\PhpJsFunctions\Var\Floatval;
use Viewi\PhpJsFunctions\Var\Gettype;
use Viewi\PhpJsFunctions\Var\Intval;
use Viewi\PhpJsFunctions\Var\IsArray;
use Viewi\PhpJsFunctions\Var\IsBinary;
use Viewi\PhpJsFunctions\Var\IsBool;
use Viewi\PhpJsFunctions\Var\IsBuffer;
use Viewi\PhpJsFunctions\Var\IsCallable;
use Viewi\PhpJsFunctions\Var\IsDouble;
use Viewi\PhpJsFunctions\Var\IsFloat;
use Viewi\PhpJsFunctions\Var\IsInt;
use Viewi\PhpJsFunctions\Var\IsInteger;
use Viewi\PhpJsFunctions\Var\IsLong;
use Viewi\PhpJsFunctions\Var\IsNull;
use Viewi\PhpJsFunctions\Var\IsNumeric;
use Viewi\PhpJsFunctions\Var\IsObject;
use Viewi\PhpJsFunctions\Var\IsReal;
use Viewi\PhpJsFunctions\Var\IsScalar;
use Viewi\PhpJsFunctions\Var\IsString;
use Viewi\PhpJsFunctions\Var\IsUnicode;
use Viewi\PhpJsFunctions\Var\_Isset;
use Viewi\PhpJsFunctions\Var\PrintR;
use Viewi\PhpJsFunctions\Var\Serialize;
use Viewi\PhpJsFunctions\Var\Strval;
use Viewi\PhpJsFunctions\Var\Unserialize;
use Viewi\PhpJsFunctions\Var\VarDump;
use Viewi\PhpJsFunctions\Var\VarExport;
use Viewi\PhpJsFunctions\Xdiff\XdiffStringDiff;
use Viewi\PhpJsFunctions\Xdiff\XdiffStringPatch;
use Viewi\PhpJsFunctions\Xml\Utf8Decode;
use Viewi\PhpJsFunctions\Xml\Utf8Encode;


return [
    '_bc' => Bc::class,
    '_phpCastString' => PhpCastString::class,
    '_php_cast_float' => PhpCastFloat::class,
    '_php_cast_int' => PhpCastInt::class,
    'array_change_key_case' => ArrayChangeKeyCase::class,
    'array_chunk' => ArrayChunk::class,
    'array_column' => ArrayColumn::class,
    'array_combine' => ArrayCombine::class,
    'array_count_values' => ArrayCountValues::class,
    'array_diff' => ArrayDiff::class,
    'array_diff_assoc' => ArrayDiffAssoc::class,
    'array_diff_key' => ArrayDiffKey::class,
    'array_diff_uassoc' => ArrayDiffUassoc::class,
    'array_diff_ukey' => ArrayDiffUkey::class,
    'array_fill' => ArrayFill::class,
    'array_fill_keys' => ArrayFillKeys::class,
    'array_filter' => ArrayFilter::class,
    'array_flip' => ArrayFlip::class,
    'array_intersect' => ArrayIntersect::class,
    'array_intersect_assoc' => ArrayIntersectAssoc::class,
    'array_intersect_key' => ArrayIntersectKey::class,
    'array_intersect_uassoc' => ArrayIntersectUassoc::class,
    'array_intersect_ukey' => ArrayIntersectUkey::class,
    'array_key_exists' => ArrayKeyExists::class,
    'array_keys' => ArrayKeys::class,
    'array_map' => ArrayMap::class,
    'array_merge' => ArrayMerge::class,
    'array_merge_recursive' => ArrayMergeRecursive::class,
    'array_multisort' => ArrayMultisort::class,
    'array_pad' => ArrayPad::class,
    'array_pop' => ArrayPop::class,
    'array_product' => ArrayProduct::class,
    'array_push' => ArrayPush::class,
    'array_rand' => ArrayRand::class,
    'array_reduce' => ArrayReduce::class,
    'array_replace' => ArrayReplace::class,
    'array_replace_recursive' => ArrayReplaceRecursive::class,
    'array_reverse' => ArrayReverse::class,
    'array_search' => ArraySearch::class,
    'array_shift' => ArrayShift::class,
    'array_slice' => ArraySlice::class,
    'array_splice' => ArraySplice::class,
    'array_sum' => ArraySum::class,
    'array_udiff' => ArrayUdiff::class,
    'array_udiff_assoc' => ArrayUdiffAssoc::class,
    'array_udiff_uassoc' => ArrayUdiffUassoc::class,
    'array_uintersect' => ArrayUintersect::class,
    'array_uintersect_uassoc' => ArrayUintersectUassoc::class,
    'array_unique' => ArrayUnique::class,
    'array_unshift' => ArrayUnshift::class,
    'array_values' => ArrayValues::class,
    'array_walk' => ArrayWalk::class,
    'array_walk_recursive' => ArrayWalkRecursive::class,
    'arsort' => Arsort::class,
    'asort' => Asort::class,
    'count' => Count::class,
    'current' => Current::class,
    'each' => Each::class,
    'end' => End::class,
    'in_array' => InArray::class,
    'key' => Key::class,
    'krsort' => Krsort::class,
    'ksort' => Ksort::class,
    'natcasesort' => Natcasesort::class,
    'natsort' => Natsort::class,
    'next' => Next::class,
    'pos' => Pos::class,
    'prev' => Prev::class,
    'range' => Range::class,
    'reset' => Reset::class,
    'rsort' => Rsort::class,
    'shuffle' => Shuffle::class,
    'sizeof' => Sizeof::class,
    'sort' => Sort::class,
    'uasort' => Uasort::class,
    'uksort' => Uksort::class,
    'usort' => Usort::class,
    'bcadd' => Bcadd::class,
    'bccomp' => Bccomp::class,
    'bcdiv' => Bcdiv::class,
    'bcmul' => Bcmul::class,
    'bcround' => Bcround::class,
    'bcscale' => Bcscale::class,
    'bcsub' => Bcsub::class,
    'ctype_alnum' => CtypeAlnum::class,
    'ctype_alpha' => CtypeAlpha::class,
    'ctype_cntrl' => CtypeCntrl::class,
    'ctype_digit' => CtypeDigit::class,
    'ctype_graph' => CtypeGraph::class,
    'ctype_lower' => CtypeLower::class,
    'ctype_print' => CtypePrint::class,
    'ctype_punct' => CtypePunct::class,
    'ctype_space' => CtypeSpace::class,
    'ctype_upper' => CtypeUpper::class,
    'ctype_xdigit' => CtypeXdigit::class,
    'checkdate' => Checkdate::class,
    'date' => Date::class,
    'date_parse' => DateParse::class,
    'getdate' => Getdate::class,
    'gettimeofday' => Gettimeofday::class,
    'gmdate' => Gmdate::class,
    'gmmktime' => Gmmktime::class,
    'gmstrftime' => Gmstrftime::class,
    'idate' => Idate::class,
    'microtime' => Microtime::class,
    'mktime' => Mktime::class,
    'strftime' => Strftime::class,
    'strptime' => Strptime::class,
    'strtotime' => Strtotime::class,
    'time' => Time::class,
    'escapeshellarg' => Escapeshellarg::class,
    'basename' => Basename::class,
    'dirname' => Dirname::class,
    'file_get_contents' => FileGetContents::class,
    'pathinfo' => Pathinfo::class,
    'realpath' => Realpath::class,
    'call_user_func' => CallUserFunc::class,
    'call_user_func_array' => CallUserFuncArray::class,
    'create_function' => CreateFunction::class,
    'function_exists' => FunctionExists::class,
    'get_defined_functions' => GetDefinedFunctions::class,
    'i18n_loc_get_default' => I18nLocGetDefault::class,
    'i18n_loc_set_default' => I18nLocSetDefault::class,
    'assert_options' => AssertOptions::class,
    'getenv' => Getenv::class,
    'ini_get' => IniGet::class,
    'ini_set' => IniSet::class,
    'set_time_limit' => SetTimeLimit::class,
    'version_compare' => VersionCompare::class,
    'json_decode' => JsonDecode::class,
    'json_encode' => JsonEncode::class,
    'json_last_error' => JsonLastError::class,
    'abs' => Abs::class,
    'acos' => Acos::class,
    'acosh' => Acosh::class,
    'asin' => Asin::class,
    'asinh' => Asinh::class,
    'atan' => Atan::class,
    'atan2' => Atan2::class,
    'atanh' => Atanh::class,
    'base_convert' => BaseConvert::class,
    'bindec' => Bindec::class,
    'ceil' => Ceil::class,
    'cos' => Cos::class,
    'cosh' => Cosh::class,
    'decbin' => Decbin::class,
    'dechex' => Dechex::class,
    'decoct' => Decoct::class,
    'deg2rad' => Deg2rad::class,
    'exp' => Exp::class,
    'expm1' => Expm1::class,
    'floor' => Floor::class,
    'fmod' => Fmod::class,
    'getrandmax' => Getrandmax::class,
    'hexdec' => Hexdec::class,
    'hypot' => Hypot::class,
    'is_finite' => IsFinite::class,
    'is_infinite' => IsInfinite::class,
    'is_nan' => IsNan::class,
    'lcg_value' => LcgValue::class,
    'log' => Log::class,
    'log10' => Log10::class,
    'log1p' => Log1p::class,
    'max' => Max::class,
    'min' => Min::class,
    'mt_getrandmax' => MtGetrandmax::class,
    'mt_rand' => MtRand::class,
    'octdec' => Octdec::class,
    'pi' => Pi::class,
    'pow' => Pow::class,
    'rad2deg' => Rad2deg::class,
    'rand' => Rand::class,
    'round' => Round::class,
    'sin' => Sin::class,
    'sinh' => Sinh::class,
    'sqrt' => Sqrt::class,
    'tan' => Tan::class,
    'tanh' => Tanh::class,
    'pack' => Pack::class,
    'uniqid' => Uniqid::class,
    'gopher_parsedir' => GopherParsedir::class,
    'inet_ntop' => InetNtop::class,
    'inet_pton' => InetPton::class,
    'ip2long' => Ip2long::class,
    'long2ip' => Long2ip::class,
    'setcookie' => Setcookie::class,
    'setrawcookie' => Setrawcookie::class,
    'preg_match' => PregMatch::class,
    'preg_quote' => PregQuote::class,
    'preg_replace' => PregReplace::class,
    'sql_regcase' => SqlRegcase::class,
    'addcslashes' => Addcslashes::class,
    'addslashes' => Addslashes::class,
    'bin2hex' => Bin2hex::class,
    'chop' => Chop::class,
    'chr' => Chr::class,
    'chunk_split' => ChunkSplit::class,
    'convert_cyr_string' => ConvertCyrString::class,
    'convert_uuencode' => ConvertUuencode::class,
    'count_chars' => CountChars::class,
    'crc32' => Crc32::class,
    'echo' => _Echo::class,
    'explode' => Explode::class,
    'get_html_translation_table' => GetHtmlTranslationTable::class,
    'hex2bin' => Hex2bin::class,
    'html_entity_decode' => HtmlEntityDecode::class,
    'htmlentities' => Htmlentities::class,
    'htmlspecialchars' => Htmlspecialchars::class,
    'htmlspecialchars_decode' => HtmlspecialcharsDecode::class,
    'implode' => Implode::class,
    'join' => Join::class,
    'lcfirst' => Lcfirst::class,
    'levenshtein' => Levenshtein::class,
    'localeconv' => Localeconv::class,
    'ltrim' => Ltrim::class,
    'md5' => Md5::class,
    'md5_file' => Md5File::class,
    'metaphone' => Metaphone::class,
    'money_format' => MoneyFormat::class,
    'nl2br' => Nl2br::class,
    'nl_langinfo' => NlLanginfo::class,
    'number_format' => NumberFormat::class,
    'ord' => Ord::class,
    'parse_str' => ParseStr::class,
    'printf' => Printf::class,
    'quoted_printable_decode' => QuotedPrintableDecode::class,
    'quoted_printable_encode' => QuotedPrintableEncode::class,
    'quotemeta' => Quotemeta::class,
    'rtrim' => Rtrim::class,
    'setlocale' => Setlocale::class,
    'sha1' => Sha1::class,
    'sha1_file' => Sha1File::class,
    'similar_text' => SimilarText::class,
    'soundex' => Soundex::class,
    'split' => Split::class,
    'sprintf' => Sprintf::class,
    'sscanf' => Sscanf::class,
    'str_getcsv' => StrGetcsv::class,
    'str_ireplace' => StrIreplace::class,
    'str_pad' => StrPad::class,
    'str_repeat' => StrRepeat::class,
    'str_replace' => StrReplace::class,
    'str_rot13' => StrRot13::class,
    'str_shuffle' => StrShuffle::class,
    'str_split' => StrSplit::class,
    'str_word_count' => StrWordCount::class,
    'strcasecmp' => Strcasecmp::class,
    'strchr' => Strchr::class,
    'strcmp' => Strcmp::class,
    'strcoll' => Strcoll::class,
    'strcspn' => Strcspn::class,
    'strip_tags' => StripTags::class,
    'stripos' => Stripos::class,
    'stripslashes' => Stripslashes::class,
    'stristr' => Stristr::class,
    'strlen' => Strlen::class,
    'strnatcasecmp' => Strnatcasecmp::class,
    'strnatcmp' => Strnatcmp::class,
    'strncasecmp' => Strncasecmp::class,
    'strncmp' => Strncmp::class,
    'strpbrk' => Strpbrk::class,
    'strpos' => Strpos::class,
    'strrchr' => Strrchr::class,
    'strrev' => Strrev::class,
    'strripos' => Strripos::class,
    'strrpos' => Strrpos::class,
    'strspn' => Strspn::class,
    'strstr' => Strstr::class,
    'strtok' => Strtok::class,
    'strtolower' => Strtolower::class,
    'strtoupper' => Strtoupper::class,
    'strtr' => Strtr::class,
    'substr' => Substr::class,
    'substr_compare' => SubstrCompare::class,
    'substr_count' => SubstrCount::class,
    'substr_replace' => SubstrReplace::class,
    'trim' => Trim::class,
    'ucfirst' => Ucfirst::class,
    'ucwords' => Ucwords::class,
    'vprintf' => Vprintf::class,
    'vsprintf' => Vsprintf::class,
    'wordwrap' => Wordwrap::class,
    'base64_decode' => Base64Decode::class,
    'base64_encode' => Base64Encode::class,
    'http_build_query' => HttpBuildQuery::class,
    'parse_url' => ParseUrl::class,
    'rawurldecode' => Rawurldecode::class,
    'rawurlencode' => Rawurlencode::class,
    'urldecode' => Urldecode::class,
    'urlencode' => Urlencode::class,
    'boolval' => Boolval::class,
    'doubleval' => Doubleval::class,
    'empty' => _Empty::class,
    'floatval' => Floatval::class,
    'gettype' => Gettype::class,
    'intval' => Intval::class,
    'is_array' => IsArray::class,
    'is_binary' => IsBinary::class,
    'is_bool' => IsBool::class,
    'is_buffer' => IsBuffer::class,
    'is_callable' => IsCallable::class,
    'is_double' => IsDouble::class,
    'is_float' => IsFloat::class,
    'is_int' => IsInt::class,
    'is_integer' => IsInteger::class,
    'is_long' => IsLong::class,
    'is_null' => IsNull::class,
    'is_numeric' => IsNumeric::class,
    'is_object' => IsObject::class,
    'is_real' => IsReal::class,
    'is_scalar' => IsScalar::class,
    'is_string' => IsString::class,
    'is_unicode' => IsUnicode::class,
    'isset' => _Isset::class,
    'print_r' => PrintR::class,
    'serialize' => Serialize::class,
    'strval' => Strval::class,
    'unserialize' => Unserialize::class,
    'var_dump' => VarDump::class,
    'var_export' => VarExport::class,
    'xdiff_string_diff' => XdiffStringDiff::class,
    'xdiff_string_patch' => XdiffStringPatch::class,
    'utf8_decode' => Utf8Decode::class,
    'utf8_encode' => Utf8Encode::class
];
