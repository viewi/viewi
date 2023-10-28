export type IMiddleware = {
    run(next: (allow: boolean) => void): void;
};