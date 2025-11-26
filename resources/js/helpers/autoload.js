const pageLoad = {};

export const registerAutoLoad = (pageName, PageClass) => {
    pageLoad[pageName] = PageClass;
}

const autoLoadPage = () => {
    const pageElement = document.querySelector('[data-page]');
    if (!pageElement) {
        return;
    }
    const pageName = pageElement.dataset.page;
    if (!pageLoad[pageName]) {
        return;
    }
    new pageLoad[pageName]();
}

if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoLoadPage);
} else {
    autoLoadPage();
}