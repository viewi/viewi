var BaseComponent = function () {
    this._props = {};
    this._element = null;
    this.emitEvent = function (name) {
        var event = arguments.length > 1 ? arguments[1] : null;
        // console.log('event has been raised:', name, event, 'ON', this);
        if (this.$_callbacks && name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
    }
}

var $base = function (instance) {
    BaseComponent.apply(instance);
}