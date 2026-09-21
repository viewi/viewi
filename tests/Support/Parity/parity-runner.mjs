// PHP↔JS parity: runs Viewi's JS function ports against the cases ParityRunner.php sends.
// stdin:  { jobs: [{ fn, source, cases: [{ args: [tagged…], refs: [argIndex…] }] }] }
// stdout: { results: { [fn]: [{ ret: tagged, refs: { [argIndex]: tagged } }] } }
// Tagged values: see Value.php. Each case runs in a fresh context so locutus globals don't leak.
import vm from 'node:vm';

process.env.TZ = 'UTC'; // PHP side runs in UTC too; Date reads TZ lazily

const input = JSON.parse(await new Promise((resolve) => {
    let data = '';
    process.stdin.setEncoding('utf8');
    process.stdin.on('data', (chunk) => data += chunk);
    process.stdin.on('end', () => resolve(data));
}));

const loneSurrogate = /[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?<![\uD800-\uDBFF])[\uDC00-\uDFFF]/;

function decodeFloat(v) {
    switch (v) {
        case 'NAN': return NaN;
        case 'INF': return Infinity;
        case '-INF': return -Infinity;
        case '-0': return -0;
    }
    return v;
}

// PHP → JS the way Viewi hands data to the browser: lists are arrays, maps are plain objects.
// Compiled inside each case's context, so the arrays/objects belong to that realm (instanceof, isArray).
// Literals only ([] and {}): v.map() would build the array in node's realm, not the context's.
const decoderSource = `(() => {
${decodeFloat.toString()}
return function decode(tagged) {
    const v = tagged.v;
    switch (tagged.t) {
        case 'null': return null;
        case 'bool':
        case 'int':
        case 'string': return v;
        case 'float': return decodeFloat(v);
        case 'const':
            // the transpiler emits the bare identifier: resolve it the way the browser would
            if (!(v in globalThis)) {
                throw new ReferenceError(v + ' is not defined');
            }
            return globalThis[v];
        case 'list': {
            const list = [];
            for (const item of v) {
                list.push(decode(item));
            }
            return list;
        }
        case 'map': {
            const obj = {};
            for (const [key, item] of v) {
                obj[key] = decode(item);
            }
            return obj;
        }
    }
    throw new Error('Parity: can not decode tag ' + tagged.t);
};
})()`;

// bytes → latin1 string up front: the context has no Buffer.
function unbytes(tagged) {
    switch (tagged.t) {
        case 'bytes': return { t: 'string', v: Buffer.from(tagged.v, 'base64').toString('latin1') };
        case 'list': return { t: 'list', v: tagged.v.map(unbytes) };
        case 'map': return { t: 'map', v: tagged.v.map(([key, item]) => [key, unbytes(item)]) };
    }
    return tagged;
}

function encode(value) {
    switch (typeof value) {
        case 'undefined': return { t: 'undefined' };
        case 'boolean': return { t: 'bool', v: value };
        case 'function': return { t: 'function' };
        case 'bigint': return { t: 'int', v: value.toString() };
        case 'number':
            if (Number.isNaN(value)) return { t: 'float', v: 'NAN' };
            if (!Number.isFinite(value)) return { t: 'float', v: value > 0 ? 'INF' : '-INF' };
            if (Object.is(value, -0)) return { t: 'float', v: '-0' };
            return { t: Number.isInteger(value) ? 'int' : 'float', v: value };
        case 'string':
            return loneSurrogate.test(value)
                ? { t: 'badstring', v: [...value].map((c) => c.codePointAt(0).toString(16)).join(' ') }
                : { t: 'string', v: value };
    }
    if (value === null) return { t: 'null' };
    if (Array.isArray(value)) return { t: 'list', v: value.map(encode) };
    const proto = Object.getPrototypeOf(value);
    if (proto === null || proto === Object.prototype || proto?.constructor?.name === 'Object') {
        // cross-context objects fail the Object.prototype check, hence the constructor name test
        return { t: 'map', v: Object.keys(value).map((key) => [key, encode(value[key])]) };
    }
    return { t: 'object', v: proto?.constructor?.name ?? 'unknown' };
}

function runCase(script, fn, testCase) {
    // Browser-like global: window is the global object, as the ports expect.
    const context = vm.createContext({});
    vm.runInContext('var window = globalThis; var global = globalThis;', context);
    try {
        script.runInContext(context);
        const target = context[fn];
        if (typeof target !== 'function') {
            return { ret: { t: 'error', v: `JS port does not define ${fn}()` }, refs: {} };
        }
        const decode = vm.runInContext(decoderSource, context);
        const args = testCase.args.map((arg) => decode(unbytes(arg)));
        const ret = target(...args);
        const refs = {};
        for (const index of testCase.refs) {
            refs[index] = encode(args[index]);
        }
        return { ret: encode(ret), refs };
    } catch (error) {
        return { ret: { t: 'error', v: String(error?.message ?? error) }, refs: {} };
    }
}

const results = {};
for (const job of input.jobs) {
    // The Builder ships each function as an ES module, and modules are strict.
    let script;
    try {
        script = new vm.Script('"use strict";\n' + job.source, { filename: job.fn + '.js' });
    } catch (error) {
        const failed = { ret: { t: 'error', v: 'JS port does not compile: ' + error.message }, refs: {} };
        results[job.fn] = job.cases.map(() => failed);
        continue;
    }
    results[job.fn] = job.cases.map((testCase) => runCase(script, job.fn, testCase));
}

process.stdout.write(JSON.stringify({ results }));
