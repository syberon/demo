/*
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */
'use strict';

import 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.dnd5';
import 'jquery.fancytree/dist/modules/jquery.fancytree.persist';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'lib/jquery-plugins/jquery.fancytree.contextMenu';
import 'jquery-contextmenu';
import 'jquery-confirm';
import 'js-cookie';

import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import 'jquery-contextmenu/dist/jquery.contextMenu.css';

import {createApp, reactive, onMounted} from 'vue';
import FlashMessenger from 'include/cms/plugins/flashmessenger';


window.addEventListener('load', function () {
    // Генерация информационного окна
    const Categories = {
        setup() {
            const item = reactive({
                name: null,
                link: null,
                id: null,
                guid: null
            });

            let tree = null;

            onMounted(() => {
                /**
                 * Инициализация дерева после загрузки приложения
                 */
                tree = $("#categoriesTree").fancytree({
                    extensions: ['dnd5', 'persist', 'contextMenu', 'table'],
                    source: {
                        url: ('/catalog/categories/gettree')
                    },
                    clickFolderMode: 1,
                    selectMode: 3,
                    titlesTabbable: true,
                    table: {
                        indentation: 20,
                        nodeColumnIdx: 0
                    },
                    persist: {
                        cookiePrefix: 'fancytree-catalog-categories-'
                    },
                    dnd5: {
                        dragStart: () => {
                            return true
                        },
                        dragEnter: () => {
                            return true
                        },

                        //dragDrop: function(node, sourceNode, hitMode, ui, draggable) {
                        dragDrop: (node, data) => {
                            data.otherNode.moveTo(node, data.hitMode);
                            let formData = new FormData;
                            formData.append('source', data.node.key);
                            formData.append('dest', data.otherNode.key);
                            formData.append('hitmode', data.hitMode);
                            fetch('/catalog/categories/reorder', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    },

                    contextMenu: {
                        menu: {
                            add: {
                                name: 'Добавить вложенную подкатегорию',
                                icon: 'add'
                            },
                            edit: {
                                name: 'Редактировать',
                                icon: 'edit'
                            },
                            delete: {
                                name: 'Удалить',
                                icon: 'delete',
                            },
                            sep2: '---------',
                            toggle: {
                                name: 'Включить/отключить',
                                icon: 'fas fa-toggle-on'
                            }
                        },
                        actions: (node, action) => {
                            nodeAction(action);
                        }
                    },

                    activate: (event, data) => loadInfo(data.node.key),
                    select: (event, data) => loadInfo(data.node.key),
                    dblclick: () => nodeAction('edit')
                });
            })

            /**
             * Загрузка информации о категории
             *
             * @param id
             */
            const loadInfo = id => {
                fetch(`/catalog/categories/getinfo/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        for (let propertyName of Object.keys(data)) {
                            item[propertyName] = data[propertyName];
                        }
                    });
            }

            /**
             * Обработка действий с категорией
             *
             * @param {String} action
             */
            const nodeAction = action => {
                switch (action) {
                    case 'add':
                        location.href = '/catalog/categories/add/' + item.id;
                        break;
                    case 'edit':
                        location.href = '/catalog/categories/edit/' + item.id;
                        break;
                    case 'delete':
                        $.confirm({
                            title: 'Подтверждение действия',
                            content: 'Удалить выбранную категорию и все вложенные?',
                            type: 'red',
                            columnClass: 'medium',
                            buttons: {
                                yes: {
                                    text: 'Да',
                                    btnClass: 'btn-red',
                                    action: () => {
                                        fetch(`/catalog/categories/delete/${item.id}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.code === 1) {
                                                    tree.fancytree("getActiveNode").remove();
                                                    item.id = null;
                                                    FlashMessenger.message('Категория успешно удалена');
                                                }
                                            });
                                    }
                                },
                                no: {
                                    text: 'Нет'
                                }
                            }
                        });
                        break;
                    case 'toggle':
                        fetch(`/catalog/categories/toggle/${item.id}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.code === 1) {
                                    tree.fancytree("getActiveNode").toggleClass('notactive');
                                }
                            });
                        break;
                }
            }

            return {
                item
            }
        }
    }

    createApp(Categories).mount('#categoriesApp');
});