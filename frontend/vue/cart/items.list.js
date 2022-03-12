/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onMounted} from 'vue';
import VueSlider from 'vue-slider-component';
import 'bootstrap-select';

import 'bootstrap-select/dist/css/bootstrap-select.css';
import 'vue-slider-component/theme/antd.css';

window.addEventListener('load', function () {
    // Генерация информационного окна
    const ItemsList = {
        components: {
            VueSlider
        },

        setup() {
            /** @type {HTMLElement} listTop */
            const listTop = ref();

            const show_filter = ref(true);
            const show_fader = ref(false);

            let delayHandler = null;
            let delayTimeout = 500;

            const itemsList = ref(null);

            const priceSlider = reactive({
                min: 0,
                max: 0,
                interval: 1
            });

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
                    value: 'name asc',
                    field: 'name',
                    direction: 'asc',
                },
                pageSize: 16
            });

            const properties = ref();

            const filterProperties = reactive({});

            const filter = ref({
                page: 1,
                category: null,
                group: null,
                price: [0, 0],
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
             * Показ/скрытие списка вариантов значений фильтра
             *
             * @param {String} code
             */
            const toggleVariants = code => {
                if (filterProperties[code] !== undefined) {
                    filterProperties[code] = !filterProperties[code];
                } else {
                    filterProperties[code] = true;
                }
            }

            /**
             * Инициализация видимости вариантов значений фильтров
             */
            const initVariantsToggler = () => {
                let types = ['items', 'offers'];

                types.forEach(type => {
                    let properties = filter.value.properties[type];
                    for (let property of Object.keys(properties)) {
                        for (let value of Object.keys(properties[property])) {
                            if (properties[property][value]) {
                                filterProperties[property] = true;
                                break;
                            }
                        }
                    }
                });
            }

            /**
             * Применение фильтра к списку
             */
            const applyFilter = () => {
                saveState();
                updateList();
            }

            /**
             * Очистка полей фильтра
             */
            const clearFilter = () => {
                sessionStorage.removeItem('items-list-filter');
                filter.value.page = 1;
                initFilters();
            }

            /**
             * Обновление списка товаров
             */
            const updateList = () => {
                show_fader.value = true;
                fetch('/catalog/items/getitems' + (filter.value.group ? '/' + filter.value.group : ''), {
                    method: 'POST',
                    body: JSON.stringify({
                        filter: filter.value,
                        params: params.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        itemsList.value = data.items;
                        filter.value.page = data.pagination.current;
                        generatePages(data.pagination.count);
                        show_fader.value = false;
                    });
            }

            /**
             * Генерация адреса содержащего данные фильтра
             */
            const generateFilterQuery = () => {
                let queryObj = {
                    price: filter.value.price,
                    properties: {}
                };
                for (let property of Object.keys(filter.value.properties.items)) {
                    let propertyValues = filter.value.properties.items[property];
                    for (let value of Object.keys(propertyValues)) {
                        if (propertyValues[value]) {
                            if (!queryObj.properties.hasOwnProperty('items')) {
                                queryObj.properties.items = {};
                            }
                            if (!queryObj.properties.items.hasOwnProperty(property)) {
                                queryObj.properties.items[property] = {};
                            }
                            queryObj.properties.items[property][value] = true;
                        }
                    }
                }

                for (let property of Object.keys(filter.value.properties.offers)) {
                    let propertyValues = filter.value.properties.offers[property];
                    for (let value of Object.keys(propertyValues)) {
                        if (propertyValues[value]) {
                            if (!queryObj.properties.hasOwnProperty('offers')) {
                                queryObj.properties.offers = {};
                            }
                            if (!queryObj.properties.offers.hasOwnProperty(property)) {
                                queryObj.properties.offers[property] = {};
                            }
                            queryObj.properties.offers[property][value] = true;
                        }
                    }
                }

                location.href = '?filter=' + encodeURIComponent(JSON.stringify(queryObj));
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

                if (filter.value.page - delta > pagesCount - pagination.range) {
                    lowerBound = pagesCount - pagination.range + 1;
                    upperBound = pagesCount;
                } else {
                    if (filter.value.page - delta < 0) {
                        delta = filter.value.page;
                    }

                    let offset = filter.value.page - delta;
                    lowerBound = offset + 1;
                    upperBound = offset + pagination.range;
                }

                for (let i = lowerBound; i <= upperBound; i++) {
                    pagination.pages.push(i);

                    // Обработка стрелок навигации
                    pagination.arrows.right = !(filter.value.page === pagesCount);
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
             * @param {number} page
             */
            const pageSet = page => {
                filter.value.page = page;
            }

            /**
             * Сохранение значений полей фильтрации
             */
            const saveState = () => {
                sessionStorage.setItem('items-list-filter', JSON.stringify(filter.value));
                sessionStorage.setItem('items-list-params', JSON.stringify(params.value));
            }

            /**
             * Рекурсивное "сливание" объектов
             *
             * @param target
             * @param sources
             * @returns {*}
             */
            const assign = (target, ...sources) => {
                // Объект является объектом и не массивом?
                function isObject(item) {
                    return item && typeof item === 'object' && !Array.isArray(item);
                }

                // Объект является массивом?
                function isShallow(item) {
                    return !(Array.isArray(item) && item.find((item) => typeof item === 'object'));
                }

                if (!sources.length) return target;
                const source = sources.shift();

                if (isObject(target) && isObject(source)) {
                    for (const key of Object.keys(source)) {
                        if (isObject(source[key])) {
                            if (!target[key]) {
                                Object.assign(target, {[key]: {}});
                            }
                            this.assign(target[key], source[key]);
                        } else {
                            if (isShallow(source[key])) {
                                Object.assign(target, {[key]: source[key]});
                            } else {
                                if (!target[key]) {
                                    Object.assign(target, {[key]: []});
                                }
                                Object.assign(target, {[key]: source[key].map((item, index) => this.assign(target[key][index] || {}, item))});
                            }
                        }
                    }
                }

                return assign(target, ...sources);
            }

            /**
             * Восстановление значений полей фильтрации
             */
            const restoreState = () => {
                const urlParams = new URLSearchParams(window.location.search);
                let urlFilter = urlParams.get('filter');
                try {
                    // Берем значения параметров фильтрации из строки запроса, если они переданы
                    if (urlFilter !== null) {
                        urlFilter = JSON.parse(decodeURIComponent(urlFilter));
                        assign(filter.value, urlFilter);
                        sessionStorage.removeItem('items-list-filter');
                    }
                    else if (sessionStorage.getItem('items-list-filter')) {
                        filter.value = JSON.parse(sessionStorage.getItem('items-list-filter'));
                    }
                } catch (e) {
                    clearFilter();
                }

                if (sessionStorage.getItem('items-list-params')) {
                    params.value = JSON.parse(sessionStorage.getItem('items-list-params'));
                }

                if (filter.value.category !== initData.category || filter.value.group !== initData.group) {
                    clearFilter();
                }
                initVariantsToggler();
            }

            /**
             * Инициализация переменных фильтров
             */
            const initFilters = () => {
                properties.value = initData.properties;


                // Генерация объектов фильтрации по свойствам наименования
                filter.value.properties.items = {};
                filter.value.properties.offers = {};

                for (let property_id of Object.keys(initData.properties.items)) {
                    let property = initData.properties.items[property_id];
                    let filter_property = {};

                    property.values.forEach(value => {
                        filter_property[value] = false;
                    });
                    filter.value.properties.items[property.code] = filter_property;
                }

                // Генерация объектов фильтрации по свойствам торговых предложений
                for (let property_id of Object.keys(initData.properties.offers)) {
                    let property = initData.properties.offers[property_id];
                    let filter_property = {};
                    property.values.forEach(value => {
                        filter_property[value] = false;
                    });
                    filter.value.properties.offers[property.code] = filter_property;
                }

                filter.value.category = initData.category;
                filter.value.group = initData.group;
                priceSlider.min = parseInt(initData.price.min);
                priceSlider.max = parseInt(initData.price.max);
                filter.value.price[0] = parseInt(initData.price.min);
                filter.value.price[1] = parseInt(initData.price.max);
            }

            onMounted(() => {
                $('.custom-selectpicker').selectpicker();
            })

            watch(() => params.value.order.value, value => {
                [params.value.order.field, params.value.order.direction] = value.split(" ");
            })

            watch(() => filter.value.page, () => {
                window.scrollTo(0, listTop.offsetTop);
            });

            watch(filter, () => {
                clearTimeout(delayHandler);
                delayHandler = setTimeout(applyFilter, delayTimeout);
            }, { deep: true })

            watch(params, () => {
                if (itemsList.value !== null) {
                    filter.value.page = 1;
                }
                clearTimeout(delayHandler);
                delayHandler = setTimeout(applyFilter, delayTimeout);
            }, { deep: true })

            watch(show_filter, () => {
                setTimeout(() => {
                    $('.custom-selectpicker').selectpicker();
                }, 100);
            })

            initFilters();
            restoreState();

            if (window.innerWidth < 769) {
                this.show_filter = false;
            }

            return {
                listTop,
                show_filter,
                show_fader,
                itemsList,
                priceSlider,
                pagination,
                params,
                filterProperties,
                filter,
                properties,
                toggleFilter,
                toggleVariants,
                clearFilter,
                generateFilterQuery,
                pageInc,
                pageDec,
                pageSet,

            }

        }
    }

    createApp(ItemsList).mount('#itemsListApp');
});
