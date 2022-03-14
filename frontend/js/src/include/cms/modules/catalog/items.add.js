/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, onMounted} from 'vue';
import 'jquery-confirm';
// noinspection ES6CheckImport
import {createTree} from 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';
import {Tab} from 'bootstrap';
import {filterFormValues} from "lib/common";

window.addEventListener('load', function () {
    // Генерация информационного окна
    const ItemsAdd = {
        setup() {
            const input = ref({
                categories: [],
                code: null,
                name: null
            });

            /**
             * Обработка отправки формы
             *
             * @param e
             * @returns {boolean}
             */
            const submitForm = e => {
                if (!input.value.categories.length) {
                    $.alert({
                        title: 'Ошибка добавления товара',
                        content: 'Выберите категории для привязки товара!',
                        type: 'red'
                    });
                    let categoriesTab = new Tab('#categories-tab');
                    categoriesTab.show()
                    e.preventDefault();
                } else {
                    return true;
                }

            }

            /**
             * Генерация символьной ссылки
             */
            const generateCode = () => {
                let formData = new FormData;
                formData.append('source', input.value.name);
                fetch('/admin/params/system/generateurl', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        input.value.code = data.url;
                    });
            }

            onMounted(() => {
                /**
                 * Инициализация дерева категорий после загрузки приложения
                 */
                createTree("#categoriesTree", {
                    extensions: ['table'],
                    source: {
                        url: ('/catalog/categories/gettree')
                    },
                    checkbox: true,
                    clickFolderMode: 3,
                    selectMode: 2,
                    activeVisible: true,
                    table: {
                        indentation: 20,
                        checkboxColumnIdx: 0,
                        nodeColumnIdx: 1
                    },

                    // Обработка выбора категории
                    select: (event, data) => {
                        input.value.categories = [];
                        data.tree.getSelectedNodes().forEach(node => {
                            input.value.categories.push(node.key);
                        });
                    }
                });
            })

            return {
                input,
                generateCode,
                submitForm
            }
        }
    }
    filterFormValues('#itemForm');
    createApp(ItemsAdd).mount('#itemsAddApp');
});