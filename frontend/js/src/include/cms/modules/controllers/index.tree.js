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

window.addEventListener('load', function () {

    let tree = $("#controllersTree").fancytree({
        extensions: ['persist', 'contextMenu', 'table'],
        checkbox: true,
        source: {
            url: ('/admin/acl/controllers/gettree'),
            cache: false
        },
        clickFolderMode: 1,
        selectMode: 3,

        debugLevel: 0,
        toggleEffect: { height: "toggle", duration: 200 },

        titlesTabbable: true,

        table: {
            indentation: 20,      // indent 20px per node level
            nodeColumnIdx: 1,     // render the node title into the 2nd column
            checkboxColumnIdx: 0
        },

        persist: {
            cookiePrefix: 'fancytree-controllers-'
        },

        renderColumns: function(event, data) {
            let node = data.node;
            let tdList = $(node.tr).find(">td");
            if (!data.node.folder){
                tdList.eq(2).text(node.data.description);
                tdList.eq(3).text(node.data.roles);
            }
        },

        contextMenu: {
            menu: {
                add: {
                    name: 'Добавить',
                    icon: 'add',
                    visible: function(key, opt){
                        return !opt.$trigger[0].ftnode.data.action;
                    }
                },
                edit: {
                    name: 'Редактировать',
                    icon: 'edit',
                    visible: function(key, opt){
                        return opt.$trigger[0].ftnode.data.action;
                    }
                },
                delete: {
                    name: 'Удалить',
                    icon: 'delete',
                    visible: function(key, opt){
                        return opt.$trigger[0].ftnode.data.action;
                    }
                },
                sep2: '---------',
                export: {
                    name: 'Экспорт',
                    icon: 'fas fa-angle-double-right'
                }
            },
            actions: (node, action) => {
                let params = [];
                switch( action ) {
                    case "add":
                        // Добавляем параметры по умолчанию для создания нового контроллера
                        if (node.data.module) {
                            params.push(`module=${node.data.module}`);
                        }
                        if (node.data.controller) {
                            params.push(`controller=${node.data.controller}`);
                        }
                        if (node.data.action) {
                            params.push(`action=${node.data.action}`);
                        }
                        location.href = '/admin/acl/controllers/add?' + params.join('&');
                        break;
                    case "edit":
                        location.href = '/admin/acl/controllers/edit/' + node.data.id;
                        break;
                    case "delete":
                        $.confirm({
                            title: 'Подтверждение действия',
                            content: 'Удалить выбранный контроллер?',
                            type: 'red',
                            columnClass: 'medium',
                            buttons: {
                                yes:  {
                                    text: 'Да',
                                    btnClass: 'btn-red',
                                    action: () => {
                                        location.href = '/admin/acl/controllers/delete/' + node.data.id;
                                    }
                                },
                                no: {
                                    text: 'Нет'
                                },
                            }
                        });
                        break;
                    case "export":
                        params = [];
                        // Добавляем параметры для экспорта
                        // Добавляем параметры по умолчанию для создания нового контроллера
                        if (node.data.module) {
                            params.push(`module=${node.data.module}`);
                        }
                        if (node.data.controller) {
                            params.push(`controller=${node.data.controller}`);
                        }
                        if (node.data.action) {
                            params.push(`action=${node.data.action}`);
                        }
                        location.href = '/admin/acl/controllers/export?' + params.join('&');
                        break;
                }
            }
        },
        dblclick: (event, data) => {
            if (data.node.data.action) {
                location.href = '/admin/acl/controllers/edit/' + data.node.data.id;
            }
        }
    });

    /**
     * Удаление списка выделенных контроллеров
     */
    document.querySelector('.btn-delete-selected').onclick = () => {

        let selected = [];
        tree.fancytree('getTree').getSelectedNodes().forEach(node => {
            selected.push(node.data.id);
        });
        let params = selected.join(',');

        if (params) {
            $.confirm({
                title: 'Подтверждение действия',
                content: 'Удалить все выбранные контроллеры?',
                type: 'red',
                columnClass: 'medium',
                buttons: {
                    yes:  {
                        text: 'Да',
                        btnClass: 'btn-red',
                        action: () => {
                            location.href = '/admin/acl/controllers/delete-selected?id=' + params;
                        }
                    },
                    no: {
                        text: 'Нет'
                    },
                }
            });
        }
    };
});