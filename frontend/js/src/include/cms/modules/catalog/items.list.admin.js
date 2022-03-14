/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, onMounted, watch} from 'vue';
import 'jquery-confirm';
import 'jquery.fancytree';
import 'jquery.fancytree/dist/modules/jquery.fancytree.persist';
import 'jquery.fancytree/dist/modules/jquery.fancytree.table';
import 'jquery.fancytree/dist/skin-win7/ui.fancytree.css';

window.addEventListener('load', function () {

    // Генерация информационного окна
    const ItemsList = {
        setup() {
            const show_filter = ref(false);
            const check_all = ref(false);

            let delayHandler = null;
            let delayTimeout = 500;

            const checkItems = ref({});
            const itemsList = ref(null);
            const pagination = reactive({
                pages: [],
                arrows: {
                    left: false,
                    right: false
                }
            });

            const params = ref({
                order: {
                    field: '',
                    direction: ''
                },
                pageSize: 50
            });

            const filter = ref({
                page: 1,
                category: 0,
                fields: {
                    name: null,
                    article: null
                },
                properties: {
                    items: {},
                    offers: {}
                }
            });


            /**
             * Показ/скрытие блока фильтрации
             */
            const toggleFilter = () => {
                show_filter.value = !show_filter.value;
            }

            /**
             * Применение фильтра к списку
             */
            const applyFilter = () => {
                clearTimeout(delayHandler);
                delayHandler = setTimeout(() => {
                    saveState();
                    show_filter.value = false;
                    updateList();
                }, delayTimeout);
            }

            /**
             * Очистка полей фильтра
             */
            const clearFilter = () => {
                sessionStorage.removeItem('items-list-admin-filter')
                filter.value.page = 1;

                for (let field of Object.keys(filter.value.fields)) {
                    filter.value.fields[field] = null;
                }

                for (let field of Object.keys(filter.value.properties.items)) {
                    filter.value.properties.items[field] = null;
                }

                for (let field of Object.keys(filter.value.properties.offers)) {
                    filter.value.properties.offers[field] = null;
                }

                show_filter.value = false;
                updateList();
            }

            /**
             * Обновление списка товаров
             */
            const updateList = () => {
                fetch('/catalog/items/getlist?unfiltered=1', {
                    method: 'POST',
                    body: JSON.stringify({
                        filter: filter.value,
                        params: params.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        checkItems.value = {};
                        for (let item of Object.values(data.items)) {
                            checkItems.value[item.id] = false;
                        }
                        itemsList.value = data.items;
                        filter.value.page = data.pagination.current;
                        generatePages(data.pagination.count);
                    });
            }

            /**
             * Генерация массива листания страниц
             */
            const generatePages = count =>  {
                pagination.pages = [];
                for (let i = 0; i < count; i++) {
                    pagination.pages.push(i + 1);

                    // Обработка стрелок навигации
                    pagination.arrows.right = !(filter.value.page === pagination.pages.length);
                    pagination.arrows.left = !(filter.value.page === 1);
                }
            }

            /**
             * Выбор всех записей в списке
             */
            const checkAll = () => {
                for (let item_id in checkItems.value) {
                    if (checkItems.value.hasOwnProperty(item_id)) {
                        checkItems.value[item_id] = check_all;
                    }
                }
            }

            /**
             * Переход на следующую страницу
             */
            const pageInc = () => {
                if (filter.value.page < pagination.pages.length) {
                    filter.value.page++;
                }
            }

            /**
             * Переход на предыдущую страницу
             */
            const pageDec = () => {
                if (filter.value.page > 1) {
                    filter.value.page--;
                }
            }

            /**
             * Установка выбранной страницы
             */
            const pageSet = page => {
                filter.value.page = page;
            }

            /**
             * Установка поля сортировки списка
             */
            const setOrder = field => {
                if (field === params.value.order.field) {
                    params.value.order.direction = (params.value.order.direction === 'asc') ? 'desc' : 'asc';
                } else {
                    params.value.order.field = field;
                    params.value.order.direction = 'asc';
                }
            }

            /**
             * Удаление записи
             *
             * @param {number} item
             */
            const deleteItem = item => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить запись?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/catalog/items/delete', {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        id: item
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 1) {
                                            updateList();
                                        }
                                    });
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }

            /**
             * Удаление выбранной записи с подтверждением действия
             *
             */
            const deleteSelected = () => {
                let items = [];

                for (let item_id in checkItems.value) {
                    if (checkItems.value.hasOwnProperty(item_id) && checkItems.value[item_id]) {
                        items.push(item_id);
                    }
                }

                if (items.length) {
                    $.confirm({
                        title: 'Подтверждение действия',
                        content: 'Удалить все выбранные записи?',
                        type: 'red',
                        columnClass: 'medium',
                        buttons: {
                            yes: {
                                text: 'Да',
                                btnClass: 'btn-red',
                                action: () => {
                                    fetch('/catalog/items/delete', {
                                        method: 'POST',
                                        body: JSON.stringify({
                                            id: items
                                        })
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 1) {
                                                updateList();
                                            }
                                        });
                                }
                            },
                            no: {
                                text: 'Нет'
                            }
                        }
                    });
                } else {
                    $.alert({
                        title: 'Информация',
                        content: 'Не выбрано ни одной записи для удаления',
                        type: 'blue',
                        columnClass: 'medium'
                    })
                }
            }

            /**
             * Сохранение значений полей фильтрации
             */
            const saveState = () => {
                sessionStorage.setItem('items-list-admin-filter', JSON.stringify(filter.value));
                sessionStorage.setItem('items-list-admin-params', JSON.stringify(params.value));
            }

            /**
             * Восстановление значений полей фильтрации
             */
            const restoreState = () => {
                if (sessionStorage.getItem('items-list-admin-filter')) {
                    filter.value = JSON.parse(sessionStorage.getItem('items-list-admin-filter'));
                }
                if (sessionStorage.getItem('items-list-admin-params')) {
                    params.value = JSON.parse(sessionStorage.getItem('items-list-admin-params'));
                }
            }

            watch(() => filter.value.category, applyFilter);
            watch(() => filter.value.page, applyFilter);
            watch(params, applyFilter, { deep: true });
            watch(check_all, checkAll);

            onMounted(() => {
                /**
                 * Инициализация дерева после загрузки приложения
                 */
                $("#categoriesTree").fancytree({
                    extensions: ['persist', 'table'],
                    source: {
                        url: ('/catalog/categories/gettree?use_root=1')
                    },
                    clickFolderMode: 1,
                    selectMode: 3,
                    titlesTabbable: true,
                    table: {
                        indentation: 20,
                        nodeColumnIdx: 0
                    },
                    persist: {
                        cookiePrefix: 'fancytree-catalog-items-admin-'
                    },
                    click: (event, data) => {
                        filter.value.category = data.node.key;
                    }
                });

                if (!filter.value.category) {
                    filter.value.category = 1;
                }
            });

            for (let item of Object.values(fields.items)) {
                filter.value.properties.items[item.code] = null;
            }
            for (let item of Object.values(fields.offers)) {
                filter.value.properties.offers[item.code] = null;
            }

            restoreState();

            return {
                show_filter,
                check_all,
                checkItems,
                itemsList,
                pagination,
                params,
                filter,
                toggleFilter,
                clearFilter,
                applyFilter,
                pageInc,
                pageDec,
                pageSet,
                setOrder,
                deleteItem,
                deleteSelected

            }


        }
    }

    createApp(ItemsList).mount('#itemsListApp');
});