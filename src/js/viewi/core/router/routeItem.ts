export class RouteItem {
    method: string;
    url: string;
    action: string;
    wheres: { [key: string]: any };
    defaults: { [key: string]: any } | null = null;

    constructor(method: string, url: string, action: string, defaults: { [key: string]: any } | null = null, wheres?: { [key: string]: any }) {
        this.method = method;
        this.url = url;
        this.action = action;
        this.wheres = {};
        this.defaults = defaults;
        if (wheres) {
            this.wheres = wheres;
        }
    }

    where(wheresOrName: string | { [key: string]: any }, expr: any) {
        if (wheresOrName !== null && typeof wheresOrName === 'object') {
            this.wheres = Object.assign(this.where, wheresOrName);
        } else if (expr) {
            this.wheres[wheresOrName] = expr;
        }
        return this;
    };
}