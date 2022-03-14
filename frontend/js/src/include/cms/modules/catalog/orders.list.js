/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onBeforeMount} from 'vue';
import 'jquery-confirm';

window.addEventListener('load', function () {

    const OrdersList = {

        setup() {
            let delayHandler = null;
            let delayTimeout = 500;

            const params = ref({
                order: {
                    field: '',
                    direction: ''
                },
                pageSize: 50
            })

            const filter = ref({
                page: 1,
                fields: {
                    id: '',
                    date: '',
                    display_name: '',
                    email: '',
                    count: '',
                    price: '',
                    price_delivery: '',
                    status: ''
                }
            })

            const items = ref([]);

            const pagination = reactive({
                pages: [],
                arrows: {
                    left: false,
                    right: false
                }
            })

            watch(filter, applyFilter, { deep: true });
            watch(params, applyFilter, { deep: true });

            onBeforeMount(() => {
                restoreState();
                loadItems();
            })

            /**
             * Применение фильтрации
             */
            const applyFilter = () => {
                clearTimeout(delayHandler);
                delayHandler = setTimeout(loadItems, delayTimeout);
            }

            /**
             * Очистка фильтров
             */
            const clearFilter = () => {
                for (let field of Object.keys(filter.value.fields)) {
                    filter.value.fields[field] = '';
                }
                sessionStorage.removeItem('orders-list-admin-filter');
            }

            /**
             * Загрузка исходного списка с сервера
             */
            const loadItems = () => {
                fetch('/catalog/orders/getitems', {
                    method: 'POST',
                    body: JSON.stringify({
                        filter: filter.value,
                        params: params.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        items.value = data.items;
                        filter.value.page = data.pagination.current;
                        generatePages(data.pagination.count);
                        saveState();
                    });
            }

            // Установка поля сортировки списка
            const setOrder = field => {
                if (field === params.value.order.field) {
                    params.value.order.direction = (params.value.order.direction === 'asc') ? 'desc' : 'asc';
                } else {
                    params.value.order.field = field;
                    params.value.order.direction = 'asc';
                }
            }

            /**
             * Удаление заказа
             */
            const deleteOrder = id => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить заказ?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch(`/catalog/orders/delete/${id}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 1) {
                                            loadItems();
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
             * Генерация массива листания страниц
             * @param count
             */
            const generatePages = count => {
                pagination.pages = [];
                for (let i = 0; i < count; i++) {
                    pagination.pages.push(i + 1);

                    // Обработка стрелок навигации
                    pagination.arrows.right = !(filter.value.page === pagination.pages.length);
                    pagination.arrows.left = !(filter.value.page === 1);
                }
            }

            // Переход на следующую страницу
            const pageInc = () => {
                if (filter.value.page < pagination.pages.length) {
                    filter.value.page++;
                }
            }

            // Переход на предыдущую страницу
            const pageDec = () => {
                if (filter.value.page > 1) {
                    filter.value.page--;
                }
            }

            // Установка выбранной страницы
            const pageSet = page => {
                filter.value.page = page;
            }

            /**
             * Сохранение значений полей фильтрации
             */
            const saveState = () => {
                sessionStorage.setItem('orders-list-admin-filter', JSON.stringify(filter.value));
                sessionStorage.setItem('orders-list-admin-params', JSON.stringify(params.value));
            }

            /**
             * Восстановление значений полей фильтрации
             */
            const restoreState = () => {
                if (sessionStorage.getItem('orders-list-admin-filter')) {
                    filter.value = JSON.parse(sessionStorage.getItem('orders-list-admin-filter'));
                }
                if (sessionStorage.getItem('orders-list-admin-params')) {
                    params.value = JSON.parse(sessionStorage.getItem('orders-list-admin-params'));
                }
            }

            return {
                filter,
                params,
                items,
                pagination,
                setOrder,
                clearFilter,
                deleteOrder,
                pageSet,
                pageDec,
                pageInc
            }
        }
    }

    createApp(OrdersList).mount('#ordersListApp');
});
