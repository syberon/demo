/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

'use strict';

// JS modules and libs
import 'bootstrap';
import 'jquery-confirm';
import 'smartmenus/dist/addons/bootstrap-4/jquery.smartmenus.bootstrap-4';
import 'include/application/main';
import {fadeOut} from "./lib/common";

// Загрузка динамических модулей
if (typeof window.modules !== 'undefined') {
    [...new Set(window.modules)].forEach(module => {
        import(
            /* webpackChunkName: "include/[request]" */
            'include/' + module + '.js'
            );
    });
}

window.CKEDITOR_BASEPATH = '/lib/ckeditor/';

window.bootstrap = require('bootstrap');

window.addEventListener('load', function () {

    // Загрузка Fancybox, если используется на странице
    if (document.querySelector('a.lightbox, [data-fancybox]')) {
        import('include/cms/plugins/fancybox');
    }

    // Загрузка обработчика AJAX-форм, если используются на странице
    if (document.querySelector('.ajax-webform')) {
        import('include/cms/modules/webform/plugin.send-ajax').then(module => {
            module.initForms();
        });
    }

    // Инициализация сообщений подтверждения
    $('.confirm').confirm({
        content: 'Удалить выбранную запись?',
        title: 'Подтверждение действия',
        type: 'red',
        columnClass: 'medium',
        buttons: {
            yes: {
                text: 'Да',
                btnClass: 'btn-red',
                action: function() {
                    location.href = this.$target.attr('href')
                }
            },
            no: {
                text: 'Нет'
            }
        }
    });

    // Отображение имен файлов в поле выбора
    for (let fileInput of document.querySelectorAll('.custom-file-input')) {
        fileInput.onchange = function () {
            let files = [];
            for (let key in this.files) {
                if (this.files.hasOwnProperty(key)) {
                    files.push(this.files[key].name);
                }
            }
            this.nextElementSibling.innerHTML = files.join(', ');
        };
    }
    console.log('page loaded');

    // Убираем прелоадер после загрузки приложения
    fadeOut('#pre-loader');
});