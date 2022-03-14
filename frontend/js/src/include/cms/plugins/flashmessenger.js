/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

'use strict';

import {createElementFromHTML, slideDown, slideUp} from 'lib/common';

function FlashMessenger() {
    let settings = {
        templates: {
            message: '<div class="message"><div class="container"><div class="text"></div><div class="icon-close"><i class="fas fa-times"></i></div></div></div>',
        },
        hideTimeout: 10000
    };

    let placeholder = null;

    /**
     * Создание сообщения
     *
     * @param message
     * @param type
     * @returns {*|jQuery|HTMLElement}
     */
    function createMessage(type, message) {
        let templateMessage = createElementFromHTML(settings.templates.message);
        templateMessage.classList.add(type);
        templateMessage.querySelector('.text').innerHTML = message;
        templateMessage.querySelector('.icon-close i').onclick = function () {
            slideUp(templateMessage, 500, () => {
                templateMessage.remove();
            });
        };
        return templateMessage;
    }

    /**
     * Отображение сообщения
     *
     * @param message
     */
    function showMessage(message) {
        setTimeout(() => {
            slideUp(message, 500, function() {
                message.remove();
            });
        }, settings.hideTimeout);
        placeholder.prepend(message);
        slideDown(message);
    }

    /**
     * Создание блока для отображения сообщений
     */
    function createPlacehoder() {
        placeholder = document.createElement('div');
        placeholder.className = 'cms-messages';
        document.body.append(placeholder);
    }

    /**
     * Динамическое добавление и отображение сообщения
     *
     * @param content
     * @param type
     */
    this.message = function (content, type = 'success') {
        if (!placeholder) {
            createPlacehoder();
        }
        let message = createMessage(type, content);
        showMessage(message);
    };

    window.addEventListener('load', () => {
        if (window.messages) {
            if (!placeholder) {
                createPlacehoder();
            }
            for (let type in window.messages) {
                if (window.messages.hasOwnProperty(type)) {
                    window.messages[type].forEach(message => {
                        let elMessage = createMessage(type, message);
                        showMessage(elMessage);

                    });
                }
            }
            window.messages = [];
        }
    });
}

export default new FlashMessenger();