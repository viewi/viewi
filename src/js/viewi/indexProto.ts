function makeProxy(component) {
    const proxy = new Proxy(component, {
        set(obj, prop, value) {
            // console.log(arguments);
            var react = obj[prop] !== value;
            var ret = Reflect.set(obj, prop, value);
            react && (prop in obj.$$r) && obj.$$r[prop]();
            return ret;
        }
    });
    proxy.$ = proxy;
    return proxy;
}

function subscribe(component, prop, callback) {
    component.$$r[prop] = callback;
}

class BaseComponent {
    _props = {};
    _name = 'BaseComponent';
    _refs = {};
    _slots = {};
    _element = null;
    $_callbacks = {};
    $$r = {};
    $;
    emitEvent(name, event) {
        if (this.$_callbacks && name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
        // console.log(`Emited event ${name}`, event);
    }
}

class Todo extends BaseComponent {
    items = [];
    count = 0;
    $total = 0; // private var simulation
    reducer;
    _name = 'Todo';
    constructor(reducer) {
        super();
        this.reducer = reducer;
        this.$ = makeProxy(this);
    }

    increment() {
        this.$.count++;
        this.emitEvent('count', this.count);
    }
}

const app = new Todo({ value: 0 });

subscribe(app, 'count', function () {
    // console.log('Count has changed', app.count);
});

app.increment();
app.increment();
app.increment();
app.increment();

const handler = app.increment;
handler.apply(app);
console.time('proxy');
for (let i = 0; i < 100000; i++) {
    const testApp = new Todo({ value: 0 });
    testApp.increment();
    const testHandler = testApp.increment;
    testHandler.apply(testApp);
}
console.timeEnd('proxy');