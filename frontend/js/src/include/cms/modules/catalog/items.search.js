/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onMounted, onBeforeMount} from 'vue';
import 'bootstrap-select';
import 'bootstrap-select/dist/css/bootstrap-select.css';

window.addEventListener('load', function () {

    const ItemsSearch = {
        setup() {
            let delayHandler = null;
            let delayTimeout = 100;

            const itemsList = ref([]);

            const query = ref(null);
            const searchCount = ref(0);

            const pagination = reactive({
                range: 6,
                count: 0,
                pages: [],
                arrows: {
                    left: false,
                    right: false
                }
            });

            const params = ref({
                order: {
                    value: 'rel desc',
                    field: 'rel',
                    direction: 'desc',
                },
                pageSize: 16,
                page: 1
            });

            watch(() => params.value.order.value, newValue => {
                [params.value.order.field, params.value.order.direction] = newValue.split(" ");
            });

            watch(params, () => {
                params.value.page = 1;
                clearTimeout(delayHandler);
                delayHandler = setTimeout(applyFilter, delayTimeout);
            }, { deep: true })

            onMounted(() => {
                $('.custom-selectpicker').selectpicker();
            })

            onBeforeMount(() => {
                restoreState();
            })


            /**
             * Применение фильтра к списку
             */
            const applyFilter = () => {
                saveState();
                updateList();
            }

            /**
             * Обновление списка товаров
             */
            const updateList = () => {

                const urlParams = new URLSearchParams(window.location.search);
                const urlQuery = urlParams.get('query');

                fetch('/catalog/search?query=' + urlQuery, {
                    method: 'POST',
                    body: JSON.stringify({
                        params: params.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        itemsList.value = data.items;
                        params.value.page = data.pagination.current;
                        generatePages(data.pagination.count);
                        query.value = urlQuery;
                        searchCount.value = data.count;
                    });
            }

            /**
             * Генерация массива листания страниц
             *
             * @param {number} pagesCount
             */
            const generatePages = pagesCount => {
                pagination.count = pagesCount;
                pagination.pages = [];
                pagination.range = 6;

                if (pagination.range > pagesCount) {
                    pagination.range = pagesCount;
                }

                let lowerBound;
                let upperBound;
                let delta = pagination.range / 2;

                if (params.value.page - delta > pagesCount - pagination.range) {
                    lowerBound = pagesCount - pagination.range + 1;
                    upperBound = pagesCount;
                } else {
                    if (params.value.page - delta < 0) {
                        delta = params.value.page;
                    }

                    let offset = params.value.page - delta;
                    lowerBound = offset + 1;
                    upperBound = offset + pagination.range;
                }

                for (let i = lowerBound; i <= upperBound; i++) {
                    pagination.pages.push(i);

                    // Обработка стрелок навигации
                    pagination.arrows.right = !(params.value.page === pagesCount);
                    pagination.arrows.left = !(params.value.page === 1);
                }
            }

            /**
             * Переход на следующую страницу
             */
            const pageInc = () => {
                if (params.value.page < pagination.pages.length) {
                    params.value.page++;
                }
            }

            /**
             * Переход на предыдущую страницу
             */
            const pageDec = () => {
                if (params.value.page > 1) {
                    params.value.page--;
                }
            }

            /**
             * Установка выбранной страницы
             * @param {number} page
             */
            const pageSet = page => {
                params.value.page = page;
            }

            /**
             * Сохранение значений полей фильтрации
             */
            const saveState = () => {
                sessionStorage.setItem('items-search-params', JSON.stringify(params.value));
            }

            /**
             * Восстановление значений полей фильтрации
             */
            const restoreState = () => {
                if (sessionStorage.getItem('items-search-params')) {
                    params.value = JSON.parse(sessionStorage.getItem('items-search-params'));
                }
                applyFilter();
            }

            return {
                itemsList,
                query,
                searchCount,
                pagination,
                params,
                pageInc,
                pageDec,
                pageSet
            }
        }
    }

    createApp(ItemsSearch).mount('#itemsSearchApp');
});
