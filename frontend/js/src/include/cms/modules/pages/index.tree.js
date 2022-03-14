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

import {createApp, reactive, ref, onMounted, watch} from 'vue';
import FlashMessenger from 'include/cms/plugins/flashmessenger';

window.addEventListener('load', function () {

    const PagesStructure = {
        setup() {
            let page = reactive({
                id: '',
                title: '',
                fullurl: '',
                redirect: '',
                layout: '',
                template: '',
            });
            let variables = ref([]);
            let tree = null;

            /**
             * Загрузка информации о разделе
             *
             * @param  page_id
             */
            const loadInfo = page_id => {
                fetch('/admin/pages/getinfo/' + page_id)
                    .then(response => response.json())
                    .then(data => {
                        ['id', 'title', 'fullurl', 'layout', 'redirect', 'template'].forEach(field => {
                            page[field] = data.page[field];
                        });
                    });
            }

            /**
             * Загрузка переменных раздела
             *
             * @param page_id
             */
            const loadVariables = page_id => {
                fetch('/admin/pages/variable/loadvarjson/' + page_id)
                    .then(response => response.json())
                    .then(data => variables.value = data.variables);
            }

            /**
             * Включение/отключение раздела
             *
             * @param page_id
             */
            const togglePage = page_id => {
                fetch('/admin/pages/toggle/' + page_id)
                    .then(response => response.json())
                    .then(() => tree.fancytree('getTree').reload());
            }

            /**
             * Удаление раздела
             *
             * @param page_id
             */
            const deletePage = page_id => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить выбранную страницу и вложенные?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/admin/pages/delete/' + page_id)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            tree.fancytree('getTree').reload();
                                            FlashMessenger.message('Раздел успешно удален');
                                        }
                                        else {
                                            FlashMessenger.message('Ошибка удаления раздела', 'error');
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

            watch(page, newValue => loadVariables(newValue.id), { deep: true });

            onMounted(() => {
                tree = $("#pagesTree").fancytree({
                    extensions: ['dnd5', 'persist', 'contextMenu', 'table'],
                    checkbox: false,
                    source: {
                        url: ('/admin/pages/gettree'),
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
                        cookiePrefix: 'fancytree-pages-'
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
                            fetch('/admin/pages/reorder', {
                                method: 'POST',
                                body: formData
                            });
                        }
                    },

                    contextMenu: {
                        menu: {
                            add: {
                                name: 'Добавить подраздел',
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
                            },
                            view: {
                                name: 'Просмотр',
                                icon: 'fas fa-binoculars'
                            },
                            sep2: '---------',
                            toggle: {
                                name: 'Включить/отключить',
                                icon: 'fas fa-toggle-on'
                            }
                        },
                        actions: (node, action) => {
                            switch (action) {
                                case "add":
                                    location.href = '/admin/pages/add/' + node.key;
                                    break;
                                case "edit":
                                    location.href = '/admin/pages/edit/' + node.key;
                                    break;
                                case "delete":
                                    deletePage(node.key);
                                    break;
                                case "view":
                                    location.href = '/admin/pages/view/' + node.key;
                                    break;
                                case "toggle":
                                    togglePage(node.key);
                            }
                        }
                    },

                    activate: (event, data) => loadInfo(data.node.key),
                    select: (event, data) => loadInfo(data.node.key),
                    dblclick: (event, data) => location.href = '/admin/pages/edit/' + data.node.key
                });
            })

            return {
                page,
                variables,
                deletePage
            }
        }
    };
    createApp(PagesStructure).mount('#pagesStructureApp');
});