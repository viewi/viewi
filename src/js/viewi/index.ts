import { ComponentsJson } from "./core/component/componentsJson";
import { componentsMeta } from "./core/component/componentsMeta";
import { setUp } from "./core/di/setUp";
import { handleUrl } from "./core/router/handleUrl";
import { watchLinks } from "./core/router/watchLinks";

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

// testing Counter
(async () => {
    const data = await (await fetch('/assets/components.json')).json() as ComponentsJson;
    componentsMeta.list = data;
    componentsMeta.router.setRoutes(data._routes);
    componentsMeta.config = data._config;
    const booleanArray = data._meta['boolean'].split(',');
    for (let i = 0; i < booleanArray.length; i++) {
        componentsMeta.booleanAttributes[booleanArray[i]] = true;
    }
    setUp();
    watchLinks();
    handleUrl(location.href);
    //setTimeout(() => renderApp('TestComponent'), 500);
})();