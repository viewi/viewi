// code extraction
let code = '';
for (let prop in $this._element) {
    const type = typeof $this._element[prop];
    console.log([prop, $this._element[prop], typeof $this._element[prop]]);
    if (type === 'function' || (type === 'object' && prop.startsWith('on'))) {
        code += `    public abstract function ${prop}();\n`;
    } else {
        code += `    public $${prop} = null;\n`;
    }
}

console.log(code);