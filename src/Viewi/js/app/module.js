var viewiGlobal = window;
function _lock(obj, property, value) {
    Object.defineProperty(obj, property, {
        value: value,
        writable: false,
        configurable: false
    });
}
_lock(viewiGlobal, 'viewiModules', {});
_lock(viewiGlobal.viewiModules, 'exports', {});
_lock(viewiGlobal.viewiModules, 'bring', function (name) {
    return viewiGlobal.viewiModules.exports[name];
});

var viewiExports = viewiGlobal.viewiModules.exports;
var viewiBring = viewiGlobal.viewiModules.bring;

// How to use:
// (function (exports, bring) {
//     // var log = bring('log'); // import

//     // exports.build = build; // export
// })(viewiExports, viewiBring);