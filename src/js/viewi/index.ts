import { ComponentsJson } from "./core/component/componentsJson";
import { componentsMeta } from "./core/component/componentsMeta";
import { handleUrl } from "./core/router/handleUrl";

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
    const booleanArray = data._meta['boolean'].split(',');
    for (let i = 0; i < booleanArray.length; i++) {
        componentsMeta.booleanAttributes[booleanArray[i]] = true;
    }
    handleUrl(location.href);
    //setTimeout(() => renderApp('TestComponent'), 500);
})();