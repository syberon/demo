/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {createApp, reactive, onMounted} from 'vue';
import Flashmessenger from 'include/cms/plugins/flashmessenger';
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


window.addEventListener('load', function () {
    // Генерация информационного окна
    const VirtualMenu = {
        setup() {
            const item = reactive({
                id: null,
                title: null,
                link: null,
                new_window: false
            });
            let tree = null;

            /**
             * Загрузка информации о пункте меню
             *
             * @param item_id
             */
            const loadInfo = item_id => {
                fetch('/admin/virtual-menu/items/getinfo/' + item_id)
                    .then(response => response.json())
                    .then(data => {
                        ['id', 'title', 'link', 'new_window'].forEach(field => {
                            item[field] = data.item[field];
                        });
                    });
            }
            /**
             * Обработка действий с пунктами меню
             *
             * @param action
             */
            const nodeAction = action => {
                switch (action) {
                    case 'add':
                        location.href = '/admin/virtual-menu/items/add/' + item.id;
                        break;
                    case 'edit':
                        location.href = '/admin/virtual-menu/items/edit/' + item.id;
                        break;
                    case 'delete':
                        $.confirm({
                            title: 'Подтверждение действия',
                            content: 'Удалить выбранный пункт и все вложенные?',
                            type: 'red',
                            columnClass: 'medium',
                            buttons: {
                                yes: {
                                    text: 'Да',
                                    btnClass: 'btn-red',
                                    action: () => {
                                        fetch('/admin/virtual-menu/items/delete/' + item.id)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.code === 1) {
                                                    tree.fancytree("getActiveNode").remove();
                                                    item.id = null;
                                                    Flashmessenger.message('Запись успешно удалена');
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
                        fetch('/admin/virtual-menu/items/toggle/' + item.id)
                            .then(response => response.json())
                            .then(data => {
                                if (data.code === 1) {
                                    tree.fancytree("getActiveNode").toggleClass('notactive');
                                }
                            });
                }
            }

            onMounted(() => {
                tree = $("#menuTree").fancytree({
                    extensions: ['dnd5', 'persist', 'contextMenu', 'table'],
                    source: {
                        url: ('/admin/virtual-menu/items/gettree')
                    },
                    clickFolderMode: 1,
                    selectMode: 3,
                    titlesTabbable: true,
                    table: {
                        indentation: 20,
                        nodeColumnIdx: 0
                    },
                    persist: {
                        cookiePrefix: 'fancytree-virtualmenu-'
                    },
                    dnd5: {
                        dragStart: () => { return true },
                        dragEnter: () => { return true },
                        dragDrop: (node, data) => {
                            data.otherNode.moveTo(node, data.hitMode);
                            let formData = new FormData;
                            formData.append('source', data.node.key);
                            formData.append('dest', data.otherNode.key);
                            formData.append('hitmode', data.hitMode);
                            fetch('/admin/virtual-menu/items/reorder', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    },

                    contextMenu: {
                        menu: {
                            add: {
                                name: 'Добавить вложенный пункт',
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
                        actions: (node, action) => nodeAction(action)
                    },

                    activate: (event, data) => loadInfo(data.node.key),
                    select: (event, data) => loadInfo(data.node.key),
                    dblclick: () => nodeAction('edit')
                });
            })

            return {
                item,
                nodeAction
            }
        }
    };

    createApp(VirtualMenu).mount('#virtualMenuApp');
});
