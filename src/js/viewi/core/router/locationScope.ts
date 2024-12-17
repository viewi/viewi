const htmlElementA = document.createElement('a');

export const locationScope: { link: HTMLAnchorElement, scrollTo: string | null, skipRender: boolean } = { link: htmlElementA, scrollTo: null, skipRender: false };