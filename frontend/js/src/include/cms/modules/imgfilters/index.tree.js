/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.persist';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'lib/jquery-plugins/jquery.fancytree.contextMenu';
import 'jquery-contextmenu';
import 'jquery-confirm';
import 'js-cookie';

import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import 'jquery-contextmenu/dist/jquery.contextMenu.css';

import {createApp, ref, onMounted} from 'vue';
import FlashMessenger from "include/cms/plugins/flashmessenger";

window.addEventListener('load', function () {

    const ImgFilters = {
        setup() {
            let tree = null;
            let filter_id = ref(null);
            let options = ref([]);

            /**
             * Загрузка параметров фильтра
             *
             * @param filter
             */
            const loadOptions = filter => {
                fetch('/admin/imgfilters/loadoptjson/' + filter)
                    .then(response => response.json())
                    .then(data => options.value = data.options);
                filter_id.value = filter;
            }

            /**
             * Удаление блока
             *
             * @param filter
             */
            const deleteFilter = filter => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить выбранный фильтр и вложенные?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/admin/imgfilters/delete/' + filter)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            tree.fancytree('getTree').reload();
                                            FlashMessenger.message('Фильтр успешно удален');
                                        } else {
                                            FlashMessenger.message('Ошибка удаления фильтра', 'error');
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
                tree = $('#filtersTree').fancytree({
                    extensions: ['persist', 'contextMenu', 'table'],
                    checkbox: false,
                    source: {
                        url: ('/admin/imgfilters/gettree'),
                        cache: false
                    },
                    clickFolderMode: 1,
                    selectMode: 1,
                    titlesTabbable: true,
                    table: {
                        indentation: 20,      // indent 20px per node level
                        nodeColumnIdx: 0     // render the node title into the 2nd column
                    },

                    renderColumns: (event, data) => {
                        let node = data.node;
                        let tdList = $(node.tr).find(">td");
                        if (!data.node.folder) {
                            tdList.eq(1).text(node.data.name);
                            tdList.eq(2).text(node.type);
                        }
                    },

                    contextMenu: {
                        menu: {
                            add: {
                                name: 'Добавить',
                                icon: 'add',
                                visible: function (key, opt) {
                                    return opt.$trigger[0].ftnode.data.parent <= 1;
                                }

                            },
                            edit: {
                                name: 'Редактировать',
                                icon: 'edit'
                            },
                            delete: {
                                name: 'Удалить',
                                icon: 'delete'
                            },
                            sep1: '---------',
                            options: {
                                name: 'Параметры',
                                icon: 'fas fa-sliders-h'
                            },
                            sep2: '---------',
                            export: {
                                name: 'Экспорт',
                                icon: 'fas fa-angle-double-right'
                            },
                            import: {
                                name: 'Импорт',
                                icon: 'fas fa-angle-double-left'
                            }
                        },
                        actions: (node, action) => {
                            switch (action) {
                                case "add":
                                    location.href = '/admin/imgfilters/add?parent=' + node.key;
                                    break;
                                case "delete":
                                    deleteFilter(node.key);
                                    break;
                                case "edit":
                                case "options":
                                case "export":
                                case "import":
                                    location.href = `/admin/imgfilters/${action}/` + node.key;
                            }
                        }
                    },

                    activate: (event, data) => loadOptions(data.node.key),
                    select: (event, data) => loadOptions(data.node.key),
                    dblclick: (event, data) => location.href = '/admin/imgfilters/edit/' + data.node.key
                });
            });

            return {
                options,
                filter_id
            }
        }
    }

    createApp(ImgFilters).mount('#imgFiltersApp');
});