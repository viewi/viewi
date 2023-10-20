class CounterReducer {
    count = 0;

    increment() {
        var $this = this;
        $this.count++;
    }

    decrement() {
        var $this = this;
        $this.count--;
    }
}

export { CounterReducer }