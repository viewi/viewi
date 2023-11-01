export type IMiddleware = {
    run(context: { next: (allow: boolean) => void }): void;
};