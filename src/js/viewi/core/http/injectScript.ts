export function injectScript(url: string) {
    const head = document.head;
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    head.appendChild(script);
}