class BaseComponent {
    __id = '';
    _props = [];
    _refs = [];
    _slots = [];

    emitEvent(eventName, event) {
        // nothing here, only client-side
    }
}

export { BaseComponent }