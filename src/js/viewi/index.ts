import { components, templates } from "../app/main/components";
import { functions } from "../app/main/functions";
import { resources } from "../app/main/resources";
import { ComponentsJson } from "./core/component/componentsJson";
import { componentsMeta } from "./core/component/componentsMeta";
import { makeGlobal } from "./core/component/makeGlobal";
import { delay } from "./core/di/delay";
import { register } from "./core/di/register";
import { setUp } from "./core/di/setUp";
import { handleUrl } from "./core/router/handleUrl";
import { watchLinks } from "./core/router/watchLinks";
import { Viewi as ViewiApp } from "./core/viewi";

const ViewiApp: ViewiApp = {
    register: {},
    version: resources.version,
    build: resources.build,
    name: resources.name,
    publish(group: string, importComponents: { [name: string]: any }) {
        for (let name in importComponents) {
            if (!(name in components)) {
                const importItem = importComponents[name];
                if (importItem._t === 'template') {
                    componentsMeta.list[importItem.name] = JSON.parse(importItem.data);
                } else {
                    components[name] = importItem;
                }
            }
        }
        delay.ready(group);
    },
};

window.ViewiApp = window.ViewiApp || {};
window.ViewiApp[resources.name] = ViewiApp;

(async () => {
    let data: ComponentsJson = JSON.parse(templates);
    if (!resources.combine) {
        const componentsResponse = await fetch(resources.componentsPath, { mode: 'same-origin' });
        if (!componentsResponse.ok) {
            throw new Error(`Failed to load components: ${componentsResponse.status} ${componentsResponse.statusText}`);
        }
        data = await componentsResponse.json() as ComponentsJson;
    }
    componentsMeta.list = data;
    componentsMeta.router.setRoutes(data._routes);
    componentsMeta.config = data._config;
    componentsMeta.globals = data._globals;
    const booleanArray = data._meta['boolean'].split(',');
    for (let i = 0; i < booleanArray.length; i++) {
        componentsMeta.booleanAttributes[booleanArray[i]] = true;
    }
    setUp(data._startup);
    makeGlobal();
    ViewiApp.register = { ...components, ...register, ...functions };
    watchLinks();
    handleUrl(location.href);
})();