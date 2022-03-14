/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

window.addEventListener('load', () => {

    // Отображение бокового меню, если оно задано для страницы
    if (document.querySelector('#actions-submenu')) {
        import('include/cms/plugins/sidemenu').then(module => {
            new module.Sidemenu('actions-submenu');
        })
    }
});