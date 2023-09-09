class TodoReducer {
    items = [];

    addNewItem(text) {
        this.items.push(text);
    }
}

export { TodoReducer }