declare global {
    interface Window { ViewiApp: { [name: string]: Viewi } }
}

export type Viewi = {
    register: { [name: string]: any },
    version: string,
    build: string,
    name: string,
    publish: (group: string, importComponents: {}) => void
};