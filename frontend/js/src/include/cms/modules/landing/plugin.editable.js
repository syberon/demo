/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {Modal} from 'bootstrap';
import {createElementFromHTML} from "lib/common";

function openEditor(id) {

    fetch(`/admin/landing/blocks/popup/${id}`)
        .then(response => { return response.text() })
        .then(content => {
            let modalEl = createElementFromHTML(content);
            modalEl.addEventListener('show.bs.modal', () => {
                modalEl.removeAttribute('tabindex');
            })

            modalEl.addEventListener('hidden.bs.modal', () => {
                modalEl.remove();
            })

            document.querySelector('body').appendChild(modalEl);
            import(
                /* webpackChunkName: 'ckeditor', webpackPrefetch: true */
                'ckeditor4'
                ).then(() => {
                CKEDITOR.replace('popup_landing', {
                    height: '430',
                    width: '100%',
                    on: {
                        'save': function () {
                            saveData(id);
                            modalInstance.hide();
                            return false;
                        }
                    }
                });
                let modalInstance = new Modal(modalEl);
                modalInstance.show();
                document.querySelector('#landing-popup-save-button').onclick = function () {
                    saveData(id);
                    modalInstance.hide();
                };
            });
        });
}

/**
 * Отправка формы
 */
function saveData(id) {
    let formData = new FormData;
    formData.append('id', id);
    formData.append('content', CKEDITOR.instances.popup_landing.getData());
    fetch(`/admin/landing/blocks/popup/${id}`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 1) {
                document.querySelector(`div.landing-block-editable[data-id='${id}']`).innerHTML = response.content;
            }
        })
}

window.addEventListener('load', function() {
    [...document.querySelectorAll('.landing-block-editable')].map(el => {
        el.addEventListener('dblclick', function () {
            openEditor(this.dataset.id);
        })
    });
});