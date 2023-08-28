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

  // app/components/BaseComponent.js
  var BaseComponent = class {
    __id = "";
    _props = [];
    _refs = [];
    _slots = [];
    emitEvent(eventName, event) {
    }
  };

  // app/components/MenuBar.js
  var MenuBar = class extends BaseComponent {
    _name = "MenuBar";
  };

  // app/components/Counter.js
  var Counter = class extends BaseComponent {
    _name = "Counter";
    count = 0;
    increment() {
      this.$.count++;
    }
    decrement() {
      this.$.count--;
    }
  };

  // app/components/HomePage.js
  var HomePage = class extends BaseComponent {
    _name = "HomePage";
    title = "Viewi v2 - Build reactive front-end with PHP";
  };

  // app/components/Layout.js
  var Layout = class extends BaseComponent {
    _name = "Layout";
    title = "Viewi";
  };

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
      this.$ = makeProxy(this);
    }
    $calculate() {
      this.$.count++;
    }
  };

  // app/functions/strlen.js
  function strlen(string) {
    var str = string + "";
    return str.length;
  }

  // app/components/StatefulTodoApp.js
  var StatefulTodoApp = class extends BaseComponent {
    _name = "StatefulTodoApp";
    text = "";
    todo = null;
    constructor(todo) {
      super();
      this.todo = todo;
      this.$ = makeProxy(this);
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

  // app/components/TodoList.js
  var TodoList = class extends BaseComponent {
    _name = "TodoList";
    items = null;
  };

  // app/components/index.js
  var components = {
    CounterReducer,
    TodoReducer,
    MenuBar,
    Counter,
    HomePage,
    Layout,
    NotFoundPage,
    CounterPage,
    TodoAppPage,
    StatefulCounter,
    StatefulTodoApp,
    TodoApp,
    TodoList,
    BaseComponent
  };

  // viewi/index.ts
  var Viewi = () => ({
    version: "2.0.1"
  });
  globalThis.Viewi = Viewi;
  for (let i in components) {
    const component = components[i];
    const instance = new component();
    console.log(component, instance);
  }
})();
