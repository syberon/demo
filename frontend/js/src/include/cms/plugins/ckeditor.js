/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

window.addEventListener('load', function () {
    import(
        /* webpackChunkName: 'ckeditor', webpackPrefetch: true */
        'ckeditor4'
        ).then(() => {
            [...document.querySelectorAll('.myckeditor')].map(el => {
                CKEDITOR.dtd.$removeEmpty['span'] = false;
                CKEDITOR.replace(el, { height: el.dataset['editorHeight'], width: el.dataset['editorWidth']})
            });
        });
});