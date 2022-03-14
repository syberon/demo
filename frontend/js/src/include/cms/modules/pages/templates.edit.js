/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

'use strict';

import 'jquery.fancytree';
import CodeMirror from 'codemirror';
import 'codemirror/mode/php/php';
import Flashmessenger from 'include/cms/plugins/flashmessenger';
import {createApp, ref, onMounted} from 'vue';


import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import 'codemirror/lib/codemirror.css';


window.addEventListener('load', function () {

    const TemplatesEdit = {
        setup() {
            let editor = null;
            let loadedFilename = null;
            const elEditor = ref();

            const b64DecodeUnicode = (str) => {
                return decodeURIComponent(atob(str).split('').map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
            }

            /**
             * Загрузка содержимого файла в редактор
             * @param {String} filename
             */
            const loadFile = filename => {
                loadedFilename = filename;
                fetch('/admin/pages/template/get?file=' + filename)
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 1) {
                            editor.setValue(b64DecodeUnicode(data.content));
                        }
                    })
            }

            /**
             * Сохранение контента в файл
             */
            const saveContent = () => {
                if (loadedFilename) {
                    let formData = new FormData;
                    formData.append('file', loadedFilename);
                    formData.append('content', editor.getValue());
                    fetch('/admin/pages/template/save', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.code === 1) {
                                Flashmessenger.message('Шаблон сохранен');
                            }
                        })
                }
            }

            onMounted(() => {
                $('.layoutTree').fancytree({
                    source: {
                        url: ('/admin/pages/gettemplates'),
                        cache: false
                    },
                    selectMode: 1,
                    toggleEffect: {height: "toggle", duration: 200},

                    // Выбор значения по двойному клику
                    dblclick: (event, data) => {
                        if (!data.node.folder) {
                            loadFile(data.node.data.file);
                        }
                    }
                });

                editor = CodeMirror(elEditor.value, {
                    mode: "php",
                    lineNumbers: true,
                    indentUnit: 4,
                    viewportMargin: Infinity
                });
            })

            return {
                elEditor,
                saveContent
            }

        },
    }

    createApp(TemplatesEdit).mount('#templateEditApp');
});