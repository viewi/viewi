import { components } from "../app/main/components";
import { functions } from "../app/main/functions";
import { resources } from "../app/main/resources";
import { ComponentsJson } from "./core/component/componentsJson";
import { componentsMeta } from "./core/component/componentsMeta";
import { delay } from "./core/di/delay";
import { register } from "./core/di/register";
import { setUp } from "./core/di/setUp";
import { handleUrl } from "./core/router/handleUrl";
import { watchLinks } from "./core/router/watchLinks";
import { Viewi } from "./core/viewi";

const Viewi: Viewi = {
    register: {},
    version: '2.0.0',
    publish(group: string, importComponents: { [name: string]: any }) {
        for (let name in importComponents) {
            if (!(name in components)) {
                const imortItem = importComponents[name];
                if (imortItem._t === 'template') {
                    componentsMeta.list[imortItem.name] = JSON.parse(imortItem.data);
                } else {
                    components[name] = imortItem;
                }
            }
        }
        delay.ready(group);
    },
};

window.ViewiApp = { Viewi };

(async () => {
    const data = await (await fetch(resources.componentsPath)).json() as ComponentsJson;
    componentsMeta.list = data;
    componentsMeta.router.setRoutes(data._routes);
    componentsMeta.config = data._config;
    const booleanArray = data._meta['boolean'].split(',');
    for (let i = 0; i < booleanArray.length; i++) {
        componentsMeta.booleanAttributes[booleanArray[i]] = true;
    }
    setUp();
    Viewi.register = { ...components, ...register, ...functions };
    watchLinks();
    handleUrl(location.href);
    //setTimeout(() => renderApp('TestComponent'), 500);
})();