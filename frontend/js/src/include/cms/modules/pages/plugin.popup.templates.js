/*
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */
'use strict';

import 'jquery.fancytree';
import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import {Modal} from 'bootstrap';
import {createElementFromHTML} from "lib/common";

window.addEventListener('load', function () {
    let template = `
        <div class="modal fade" id="templates-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Выбор шаблона страницы</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="treeObject"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-select">Выбрать</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>        
    `;

    let selector = '.get-template-field';
    let elTarget;
    let elDialogInstance;

    /**
     * Создание диалога
     */
    function createDialog() {
        let elDialog = createElementFromHTML(template);
        document.querySelector('body').appendChild(elDialog);
        elDialogInstance = new Modal(elDialog);

        let elTree = $('.treeObject', elDialog).fancytree({
            source: {
                url: ('/admin/pages/gettemplates'),
                cache: false
            },
            selectMode: 1,
            // Выбор значения по двойному клику
            dblclick: (event, data) => setValue(data.tree.getActiveNode())
        });
        elDialog.querySelector('.btn-select').onclick = () => {
            setValue(elTree.fancytree('getTree').getActiveNode());
        }
    }

    /**
     * Установка выбранного значения
     */
    function setValue(node) {
        if (node && !node.folder) {
            document.querySelector(`[name='${elTarget}']`).value = node.data.file;
            elDialogInstance.hide();
        }
    }

    [...document.querySelectorAll(selector)].map(el => {
        el.addEventListener('click', function () {
            if (!elDialogInstance) {
                createDialog();
            }
            elTarget = this.dataset['target'];
            elDialogInstance.show();
        })
    });
});