(() => {
  // viewi/core/render.ts
  var document2 = globalThis.document;
  document2._c = document2.createElement;
  function render(target2, component) {
    console.log("Rendering", target2, component);
    var div = document2._c("div");
    var span = document2._c("span");
    span.textContent = component.value || "";
    div.appendChild(span);
    var button = document2._c("button");
    button.textContent = "Switch";
    var button2 = document2._c("button");
    button2.textContent = "Clicked " + component.count + " times";
    target2.appendChild(div);
    target2.appendChild(button);
    target2.appendChild(button2);
    component.$$r = {
      "count": () => button2.textContent = "Clicked " + component.count + " times",
      "value": () => span.textContent = component.value || ""
    };
    var dispose = [
      button.addEventListener("click", component.onClick),
      button2.addEventListener("click", component.increment)
    ];
    console.log(component);
  }

  // viewi/core/baseComponent.ts
  function BaseComponent() {
    this._props = {};
    this._refs = {};
    this._slots = {};
    this._element = null;
    this.$_callbacks = {};
    this.emitEvent = function(name, event) {
      if (this.$_callbacks && name in this.$_callbacks) {
        this.$_callbacks[name](event);
      }
    };
  }

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
    proxy.$$p = proxy;
    return proxy;
  }

  // viewi/tests/HomeComponent.ts
  function HomeComponent() {
    BaseComponent.apply(this);
    const $this = makeProxy(this);
    this.value = null;
    this.count = 0;
    this.dynamicTagOrComponent1 = "ListItem";
    this.dynamicTagOrComponent2 = "span";
    this.getName = function() {
      return "My name " + ($this.value || "Anon");
    };
    this.onClick = function(event) {
      console.log("Clicked");
      $this.value = $this.value ? null : "My Text";
    };
    this.increment = function() {
      $this.count++;
    };
  }

  // viewi/index.ts
  var Viewi = () => ({
    version: "2.0.0"
  });
  globalThis.Viewi = Viewi;
  var homeComponent = new HomeComponent().$$p;
  console.log(homeComponent);
  globalThis.homeComponent = homeComponent;
  var target = document.getElementById("app");
  if (target !== null) {
    render(target, homeComponent);
  }
  setInterval(() => homeComponent.count++, 1e3);
  var baseComponent = {
    create(child) {
      const base = child || {};
      base._name_ = "BaseComponent";
      base._props = {};
      base.$$r = {};
      base.$$p = null;
      base._refs = {};
      base._slots = {};
      base._element = null;
      base.$_callbacks = {};
      base.emitEvent = function(name, event) {
        if (this.$_callbacks && name in this.$_callbacks) {
          this.$_callbacks[name](event);
        }
      };
      return base;
    }
  };
  function makeProxy2(component) {
    const proxy = new Proxy(component, {
      set(obj, prop, value) {
        var react = obj[prop] !== value;
        var ret = Reflect.set(obj, prop, value);
        react && prop in obj.$$r && obj.$$r[prop]();
        return ret;
      }
    });
    proxy.$$p = proxy;
    return proxy;
  }
  var todoApp = {
    create(child) {
      let a = 1;
      const base = child || {};
      base._name_ = "TodoApp";
      baseComponent.create(base);
      const $this = makeProxy2(base);
      base.count = 0;
      base.text = "";
      base.items = [];
      base.handleSubmit = function(event) {
        event.preventDefault();
        if ($this.text.length == 0) {
          return;
        }
        $this.items.push($this.text);
        $this.text = "";
      };
      base.increment = function() {
        $this.count++;
        return ++a;
      };
      return $this;
    }
  };
  var b = todoApp.create();
  var c = todoApp.create();
  b.$$r["count"] = () => console.log("Count has changed", b.count);
  b.increment();
  b.increment();
  b.increment();
  b.increment();
  console.log(b.increment(), c.increment());
})();
