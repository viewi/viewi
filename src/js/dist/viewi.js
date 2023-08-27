(() => {
  // viewi/core/BaseComponent.ts
  var BaseComponent = class {
    _props = {};
    $_callbacks = {};
    _refs = {};
    _slots = {};
    _element = null;
    $$r = {};
    $;
    _name = "BaseComponent";
    emitEvent(name, event) {
      if (this.$_callbacks && name in this.$_callbacks) {
        this.$_callbacks[name](event);
      }
    }
  };

  // viewi/core/makeProxy.ts
  function makeProxy(component) {
    const proxy = new Proxy(component, {
      set(obj, prop, value) {
        var react = obj[prop] !== value;
        var ret = Reflect.set(obj, prop, value);
        react && prop in obj.$$r && obj.$$r[prop]();
        return ret;
      }
    });
    component.$ = component;
    return proxy;
  }

  // viewi/index.ts
  var Viewi = () => ({
    version: "2.0.0"
  });
  globalThis.Viewi = Viewi;
  var Todo = class extends BaseComponent {
    items = [];
    count = 0;
    $total = 0;
    // private var simulation
    reducer;
    _name = "Todo";
    constructor(reducer) {
      super();
      this.reducer = reducer;
      this.$ = makeProxy(this);
    }
    increment() {
      this.$.count++;
      this.emitEvent("count", this.count);
    }
  };
  var b = new Todo({ count: 0 });
  var c = new Todo({ count: 0 });
  b.$$r["count"] = () => console.log("Count has changed", b.count);
  b.$_callbacks["count"] = (event) => console.log("Event count", event);
  b.increment();
  b.increment();
  b.increment();
  b.increment();
  console.log(b.increment(), c.increment());
})();
