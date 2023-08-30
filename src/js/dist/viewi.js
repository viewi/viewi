(() => {
  // app/components/CounterReducer.js
  var CounterReducer = class {
    count = 0;
    increment() {
      this.$.count++;
    }
    decrement() {
      this.$.count--;
    }
  };

  // app/components/TodoReducer.js
  var TodoReducer = class {
    items = [];
    addNewItem(text) {
      this.$.items.push(text);
    }
  };

  // viewi/core/BaseComponent.ts
  var BaseComponent = class {
    _props = {};
    $_callbacks = {};
    _refs = {};
    _slots = {};
    _element = null;
    $$r = [];
    $;
    _name = "BaseComponent";
    emitEvent(name, event) {
      if (this.$_callbacks && name in this.$_callbacks) {
        this.$_callbacks[name](event);
      }
    }
  };

  // app/components/MenuBar.js
  var MenuBar = class extends BaseComponent {
    _name = "MenuBar";
  };

  // app/functions/strlen.js
  function strlen(string) {
    var str = string + "";
    return str.length;
  }

  // app/components/Counter.js
  var Counter = class extends BaseComponent {
    _name = "Counter";
    count = 0;
    message = "My message";
    increment() {
      this.$.count++;
    }
    decrement() {
      this.$.count--;
    }
  };
  var Counter_x = [
    function(_component) {
      return _component.count;
    },
    function(_component) {
      return strlen(_component.message);
    }
  ];

  // app/components/HomePage.js
  var HomePage = class extends BaseComponent {
    _name = "HomePage";
    title = "Viewi v2 - Build reactive front-end with PHP";
  };
  var HomePage_x = [
    function(_component) {
      return _component.title;
    },
    function(_component) {
      return _component.title;
    }
  ];

  // app/components/Layout.js
  var Layout = class extends BaseComponent {
    _name = "Layout";
    title = "Viewi";
  };
  var Layout_x = [
    function(_component) {
      return _component.title;
    }
  ];

  // app/components/NotFoundPage.js
  var NotFoundPage = class extends BaseComponent {
    _name = "NotFoundPage";
  };

  // app/components/CounterPage.js
  var CounterPage = class extends BaseComponent {
    _name = "CounterPage";
  };

  // app/components/TodoAppPage.js
  var TodoAppPage = class extends BaseComponent {
    _name = "TodoAppPage";
  };

  // app/components/StatefulCounter.js
  var StatefulCounter = class extends BaseComponent {
    _name = "StatefulCounter";
    counter = null;
    $message = "Secret message";
    count = null;
    constructor(count2) {
      super();
      this.count = count2 === void 0 ? 0 : count2;
      this.counter = new CounterReducer();
    }
    $calculate() {
      this.$.count++;
    }
  };
  var StatefulCounter_x = [
    function(_component) {
      return _component.__id;
    },
    function(_component) {
      return _component.counter.count;
    }
  ];

  // app/functions/count.js
  function count(mixedVar, mode) {
    let key;
    let cnt = 0;
    if (mixedVar === null || typeof mixedVar === "undefined") {
      return 0;
    } else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
      return 1;
    }
    if (mode === "COUNT_RECURSIVE") {
      mode = 1;
    }
    if (mode !== 1) {
      mode = 0;
    }
    for (key in mixedVar) {
      if (mixedVar.hasOwnProperty(key)) {
        cnt++;
        if (mode === 1 && mixedVar[key] && (mixedVar[key].constructor === Array || mixedVar[key].constructor === Object)) {
          cnt += count(mixedVar[key], 1);
        }
      }
    }
    return cnt;
  }

  // app/components/StatefulTodoApp.js
  var StatefulTodoApp = class extends BaseComponent {
    _name = "StatefulTodoApp";
    text = "";
    todo = null;
    constructor(todo) {
      super();
      this.todo = todo;
    }
    handleSubmit(event) {
      event.preventDefault();
      if (strlen(this.$.text) == 0) {
        return;
      }
      this.$.todo.addNewItem(this.$.text);
      this.$.text = "";
    }
  };
  var StatefulTodoApp_x = [
    function(_component) {
      return _component.text;
    },
    function(_component) {
      return count(_component.todo.items) + 1;
    },
    function(_component) {
      return _component.todo.items;
    }
  ];

  // app/components/TodoApp.js
  var TodoApp = class extends BaseComponent {
    _name = "TodoApp";
    text = "";
    items = [];
    handleSubmit(event) {
      event.preventDefault();
      if (strlen(this.$.text) == 0) {
        return;
      }
      this.$.items.push(this.$.text);
      this.$.text = "";
    }
  };
  var TodoApp_x = [
    function(_component) {
      return _component.text;
    },
    function(_component) {
      return count(_component.items) + 1;
    },
    function(_component) {
      return _component.items;
    }
  ];

  // app/components/TodoList.js
  var TodoList = class extends BaseComponent {
    _name = "TodoList";
    items = null;
  };
  var TodoList_x = [
    function(_component) {
      return item;
    }
  ];

  // app/components/index.js
  var components = {
    CounterReducer,
    TodoReducer,
    MenuBar,
    Counter_x,
    Counter,
    HomePage_x,
    HomePage,
    Layout_x,
    Layout,
    NotFoundPage,
    CounterPage,
    TodoAppPage,
    StatefulCounter_x,
    StatefulCounter,
    StatefulTodoApp_x,
    StatefulTodoApp,
    TodoApp_x,
    TodoApp,
    TodoList_x,
    TodoList
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

  // viewi/core/unpack.ts
  function unpack(item2) {
    let nodeType = "value";
    switch (item2.t) {
      case "t": {
        nodeType = "tag";
        break;
      }
      case "a": {
        nodeType = "attr";
        break;
      }
      case void 0:
      case "v": {
        nodeType = "value";
        break;
      }
      case "c": {
        nodeType = "component";
        break;
      }
      case "x": {
        nodeType = "text";
        break;
      }
      case "m": {
        nodeType = "comment";
        break;
      }
      default:
        throw new Error("Type " + item2.t + " is not defined in build");
    }
    item2.type = nodeType;
    delete item2.t;
    if (item2.c) {
      item2.content = item2.c;
      delete item2.c;
    }
    if (item2.e) {
      item2.expression = item2.e;
      delete item2.e;
    }
    if (item2.a) {
      item2.attributes = item2.a;
      delete item2.a;
    }
    if (item2.h) {
      item2.children = item2.h;
      delete item2.h;
    }
    ;
  }

  // viewi/index.ts
  var componentsMeta = {};
  var Viewi = () => ({
    version: "2.0.1"
  });
  globalThis.Viewi = Viewi;
  console.log("Viewi entry");
  var counterTarget = document.getElementById("counter");
  function render(target, instance, nodes) {
    for (let i in nodes) {
      const node = nodes[i];
      if (!node.unpacked) {
        unpack(node);
        node.unpacked = true;
      }
      let element = target;
      let content = node.expression ? instance.$$r[node.code](instance) : node.content ?? "";
      switch (node.type) {
        case "tag": {
          element = document.createElement(content);
          target.appendChild(element);
          console.log("tag", node);
          break;
        }
        case "text": {
          const textNode = document.createTextNode(content);
          target.appendChild(textNode);
          console.log("text", node);
          break;
        }
        default: {
          break;
        }
      }
      if (node.children) {
        render(element, instance, node.children);
      }
    }
  }
  function renderComponent(name) {
    if (!(name in componentsMeta)) {
      throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
      throw new Error(`Component ${name} not found.`);
    }
    const root = componentsMeta[name].nodes;
    const instance = makeProxy(new components[name]());
    const inlineExpressions = name + "_x";
    if (inlineExpressions in components) {
      instance.$$r = components[inlineExpressions];
    }
    if (counterTarget && root) {
      const rootChildren = root.h;
      console.log(counterTarget, instance, rootChildren);
      rootChildren && render(counterTarget, instance, rootChildren);
    }
  }
  (async () => {
    componentsMeta = await (await fetch("/assets/components.json")).json();
    renderComponent("Counter");
  })();
})();
