/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

'use strict';

import 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'jquery-confirm';
import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';

window.addEventListener('load', function () {
    let tree = $('#controllersTree').fancytree({
        extensions: ['table'],
        checkbox: true,
        source: {
            url: ('/admin/acl/controllers/gettree?link=1&role=' + window.role_id),
            cache: false
        },
        clickFolderMode: 2,
        selectMode: 3,
        titlesTabbable: true,
        table: {
            indentation: 20,      // indent 20px per node level
            nodeColumnIdx: 1,     // render the node title into the 2nd column
            checkboxColumnIdx: 0
        },

        renderColumns: function(event, data) {
            let node = data.node;
            let tdList = $(node.tr).find(">td");
            if (!data.node.folder){
                tdList.eq(2).text(node.data.description);
            }
        },
    });

    /**
     * Сохранение выбранных контроллеров для роли
     */
    $('.btn-save-selected').on('click', function() {

        let selected = [];
        tree.fancytree('getTree').getSelectedNodes().forEach(node => {
            if (typeof(node.data.id) != 'undefined') {
                selected.push(node.data.id);
            }
        });
        let params = selected.join(',');

        $.confirm({
            title: 'Подтверждение действия',
            content: 'Сохранить назначенные роли?',
            type: 'blue',
            columnClass: 'medium',
            buttons: {
                yes:  {
                    text: 'Да',
                    btnClass: 'btn-blue',
                    action: function(){
                        location.href = `/admin/acl/controllers/assign/${window.role_id}?id=${params}`;
                    }
                },
                no: {
                    text: 'Нет'
                },
            }
        });
    })
});

