class CounterReducer {
    count = 0;

    increment() {
        this.count++;
    }

    decrement() {
        this.count--;
    }
}

export { CounterReducer }