/*
 * Copyright (c) 2019.
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
import FlashMessenger from "include/cms/plugins/flashmessenger";

window.addEventListener('load', function () {

    const ContentBlocks = {
        setup() {
            let tree = null;
            let block = reactive({
                id: '',
                title: '',
                fullurl: '',
                template: ''
            });

            /**
             * Загрузка параметров блока
             *
             * @param block_id
             */
            const loadInfo = block_id => {
                fetch('/admin/content/getinfo/' + block_id)
                    .then(response => response.json())
                    .then(data => {
                        ['id', 'title', 'fullurl', 'template'].forEach(field => {
                            block[field] = data.content[field];
                        });
                    });
            }

            /**
             * Удаление блока
             *
             * @param block_id
             */
            const deleteBlock = block_id => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить выбранный блок и вложенные?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/admin/content/delete/' + block_id)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            tree.fancytree('getTree').reload();
                                            FlashMessenger.message('Блок успешно удален');
                                        } else {
                                            FlashMessenger.message('Ошибка удаления блока', 'error');
                                        }
                                    });
                            }
                        },
                        no: {
                            text: 'Нет'
                        },
                    }
                });
            }

            onMounted(() => {
                tree = $("#blocksTree").fancytree({
                    extensions: ['dnd5', 'persist', 'contextMenu', 'table'],
                    checkbox: false,
                    source: {
                        url: ('/admin/content/gettree'),
                        cache: false
                    },
                    clickFolderMode: 1,
                    selectMode: 3,
                    titlesTabbable: true,

                    table: {
                        indentation: 20, // indent 20px per node level
                        nodeColumnIdx: 0 // render the node title into the 2nd column
                    },

                    persist: {
                        cookiePrefix: 'fancytree-content-'
                    },

                    dnd5: {
                        dragStart: () => {
                            return true
                        },
                        dragEnter: () => {
                            return true
                        },
                        dragDrop: (node, data) => {
                            data.otherNode.moveTo(node, data.hitMode);
                            let formData = new FormData;
                            formData.append('source', data.node.key);
                            formData.append('dest', data.otherNode.key);
                            formData.append('hitmode', data.hitMode);
                            fetch('/admin/content/reorder', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    },

                    contextMenu: {
                        menu: {
                            add: {
                                name: 'Добавить',
                                icon: 'add'
                            },
                            edit: {
                                name: 'Редактировать',
                                icon: 'edit'
                            },
                            delete: {
                                name: 'Удалить',
                                icon: 'delete',
                                visible: function (key, opt) {
                                    return opt.$trigger[0].ftnode.data.parent;
                                }
                            }
                        },
                        actions: (node, action) => {
                            switch (action) {
                                case "add":
                                    location.href = '/admin/content/add/' + node.key;
                                    break;
                                case "edit":
                                    location.href = '/admin/content/edit/' + node.key;
                                    break;
                                case "delete":
                                    deleteBlock(node.key)
                            }
                        }
                    },

                    activate: (event, data) => loadInfo(data.node.key),
                    select: (event, data) => loadInfo(data.node.key),
                    dblclick: (event, data) => location.href = '/admin/content/edit/' + data.node.key
                });
            });

            return {
                block
            }
        }
    }

    createApp(ContentBlocks).mount('#contentBlocksApp');
});