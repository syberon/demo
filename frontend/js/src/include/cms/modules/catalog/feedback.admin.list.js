/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onBeforeMount} from 'vue';
import 'jquery-confirm';

window.addEventListener('load', function () {

    // Генерация информационного окна
    const FeedbackList = {
        setup() {
            let delayHandler = null;
            let delayTimeout = 500;

            const pagination = reactive({
                pages: [],
                arrows: {
                    left: false,
                    right: false
                }
            })

            const items = ref([]);

            const params = ref({
                order: {
                    field: '',
                    direction: ''
                },
                pageSize: 50,
            })

            const filter = ref({
                page: 1,
                fields: {
                    date: '',
                    username: '',
                    item_name: '',
                    rate: '',
                    active: ''
                }
            })

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

            watch(filter, applyFilter, { deep: true });
            watch(params, applyFilter, { deep: true })

            /**
             * Загрузка исходного списка с сервера
             */
            const loadItems = () => {
                fetch('/catalog/feedback/getadminlist', {
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

            /**
             * Генерация массива листания страниц
             *
             * @param {number} count
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
             *
             * @param {number} page
             */
            const pageSet = page => {
                filter.value.page = page;
            }

            /**
             * Установка поля сортировки списка
             *
             * @param field
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
             * Удаление отзыва
             */
            const deleteFeedback = id => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить отзыв?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch(`/catalog/feedback/delete/${id}`)
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
             * Сохранение значений полей фильтрации
             */
            const saveState = () => {
                sessionStorage.setItem('feedback-list-admin-filter', JSON.stringify(filter.value));
                sessionStorage.setItem('feedback-list-admin-params', JSON.stringify(params.value));
            }

            /**
             * Восстановление значений полей фильтрации
             */
            const restoreState = () => {
                if (sessionStorage.getItem('feedback-list-admin-filter')) {
                    filter.value = JSON.parse(sessionStorage.getItem('feedback-list-admin-filter'));
                }
                if (sessionStorage.getItem('feedback-list-admin-params')) {
                    params.value = JSON.parse(sessionStorage.getItem('feedback-list-admin-params'));
                }
            }

            /**
             * Очистка фильтров
             */
            const clearFilter = () => {
                for (let field of Object.keys(filter.value.fields)) {
                    filter.value.fields[field] = '';
                }
                sessionStorage.removeItem('feedback-list-admin-filter');
            }

            return {
                items,
                pagination,
                params,
                filter,
                setOrder,
                clearFilter,
                deleteFeedback,
                pageInc,
                pageDec,
                pageSet
            }
        }
    }
    createApp(FeedbackList).mount('#feedbackListApp');
});
