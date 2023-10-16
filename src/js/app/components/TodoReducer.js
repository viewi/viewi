class TodoReducer {
    items = [];

    addNewItem(text) {
        this.items = [...this.items, text];
    }
}

export { TodoReducer }