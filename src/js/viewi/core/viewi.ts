declare global {
    interface Window { ViewiApp: { [name: string]: Viewi } }
}

export type Viewi = {
    register: { [name: string]: any },
    version: string,
    publish: (group: string, importComponents: {}) => void
};