class TodoReducer {
    items = [];

    addNewItem(text) {
        var $this = this;
        $this.items = [...$this.items, text];
    }
}

export { TodoReducer }