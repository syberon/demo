/*
 * Copyright (c) 2020.
 *
 * @author Syber
 * @email syberon@gmail.com
 */
'use strict';

import {createApp, ref, onMounted, watch} from 'vue';
import 'jquery-confirm';
import Sortable from 'sortablejs';
import CodeMirror from "codemirror";
import 'codemirror/mode/css/css';
import 'codemirror/mode/sass/sass';
import 'codemirror/addon/edit/closebrackets';
import 'codemirror/addon/edit/matchbrackets';
import Flashmessenger from 'include/cms/plugins/flashmessenger';
import 'codemirror/lib/codemirror.css';


window.addEventListener('load', function () {

    const CssManage = {
        setup() {
            const currentBlock = ref(null);
            const content = ref();
            const blocks = ref();
            let editor = null;
            let initialContent = '';

            /**
             * Выбор активного блока для редактирования
             *
             * @param block_id
             */
            const setActiveBlock = block_id => {
                if (editor.getValue() !== initialContent) {
                    // Инициализация сообщений подтверждения
                    $.confirm({
                        content: 'Контент данного блока был изменен. Сохранить изменения?',
                        title: 'Подтверждение действия',
                        type: 'green',
                        columnClass: 'medium',
                        buttons: {
                            yes: {
                                text: 'Да',
                                btnClass: 'btn-green',
                                action: () => {
                                    saveContent();
                                    currentBlock.value = block_id;
                                }
                            },
                            no: {
                                text: 'Нет',
                                action: () => {
                                    currentBlock.value = block_id;
                                }
                            }
                        }
                    });
                } else {
                    currentBlock.value = block_id;
                }
            }

            /**
             * Загрузка списка записей для выбранной категории
             */
            const loadContent = () => {
                fetch('/admin/params/css/load/' + currentBlock.value)
                    .then(response => response.json())
                    .then(data => {
                        editor.setValue(data.block.content ? data.block.content : '');
                        initialContent = editor.getValue();
                    });
            }

            const saveContent = () => {
                let formData = new FormData;
                formData.append('content', editor.getValue());
                fetch('/admin/params/css/update/' + currentBlock.value, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(() => {
                        initialContent = editor.getValue();
                        Flashmessenger.message('Изменения сохранены');
                    });
            }

            const confirmDelete = id => {
                // Инициализация сообщений подтверждения
                $.confirm({
                    content: 'Удалить выбранную запись?',
                    title: 'Подтверждение действия',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: function () {
                                location.href = '/admin/params/css/delete/' + id;
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }

            watch(currentBlock, loadContent);

            onMounted(() => {
                editor = CodeMirror(content.value, {
                    mode: "sass",
                    lineNumbers: true,
                    indentUnit: 4,
                    matchBrackets: true,
                    autoCloseBrackets: true,
                    viewportMargin: Infinity

                });
                editor.setSize('100%', 800);

                if (blocks.value.dataset.first_block) {
                    setActiveBlock(blocks.value.dataset.first_block);
                }


                Sortable.create(blocks.value, {
                    handle: '.handle',
                    onEnd: function () {
                        let formData = new FormData;
                        formData.append('listValues', this.toArray());
                        fetch('/admin/params/css/reorder', {
                            method: 'POST',
                            body: formData
                        })
                    }
                })

            })


            return {
                currentBlock,
                blocks,
                content,
                setActiveBlock,
                confirmDelete,
                saveContent
            }
        }
    }

    createApp(CssManage).mount('#appCssManage');
});