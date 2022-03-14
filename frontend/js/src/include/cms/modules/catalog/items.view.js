/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import 'jquery-confirm';
import { Fancybox } from "@fancyapps/ui";
import ru from "@fancyapps/ui/src/Fancybox/l10n/ru"
import "@fancyapps/ui/dist/fancybox.css";

import eventBus from 'include/cms/plugins/vue.eventbus';
import {createApp, ref, reactive, computed, watch, onMounted} from 'vue';
import useFeedback from './items.useFeedback';

window.addEventListener('load', function () {

    // Генерация информационного окна
    const ItemView = {
        setup() {
            const pictures = ref([]);
            const mainPicture = reactive({
                src: null,
                link: null
            });

            const offers = ref([]);
            const properties = reactive({});
            let selected = reactive({});
            let current_property = null;
            const ordered = reactive({
                properties: [],
                values: {}
            })

            const itemsRelated = ref([]);

            const {
                feedback,
                feedbackItemsList,
                showFeedbackBlock,
                showFeedbackForm,
                showAllFeedbacks,
                hoverItem,
                resetRate,
                setRate,
                submitFeedback
            } = useFeedback(initData.item_id);

            // Список изображений для формирования скрытой галереи
            const gallery_pictures = computed(() => {
                let result = [];
                pictures.value.forEach(
                    /** @param {Object} picture */
                    picture => {
                    if (picture.main !== mainPicture.src) {
                        result.push(picture);
                    }
                });
                return result;

            });

            // Преобрзованный список торговых предложений
            const transformed = computed(() => {
                let transformedOffers = [];
                offers.value.forEach(
                    /** @param {Object} value */
                    value => {
                    let offer = {
                        id: value.id,
                        properties: {},
                        stock: value.stock
                    };
                    value.properties.forEach(property => {
                        offer.properties[property.code] = property.value;
                    });
                    transformedOffers.push(offer);
                });
                return transformedOffers;
            });

            // Список отфильтрованных, в заисимости от выбранных значений свойств, торговых предложений
            const filtered = computed(() => {
                let filtered = [];
                transformed.value.forEach(offer => {
                    if (!Object.entries(selected).filter(([property, value]) => {
                        return value && offer.properties[property] !== value;
                    }).length) {
                        filtered.push(offer);
                    }
                });
                return filtered;
            });

            // Информация о текущем выбранном торговом предложении
            const selectedOffer = computed(() => {
                let selectedOffer = {};
                offers.value.forEach(
                    /** @param {Object} item */
                    item => {
                    if (filtered.value.length > 0 && item.id === filtered.value[0].id) {
                        selectedOffer.offer = item.id;
                        selectedOffer.item = item.item;
                        selectedOffer.price = item.price.price;
                        selectedOffer.price_sale = item.price.price_sale;
                        selectedOffer.count = item.stock.count;
                    }
                });
                return selectedOffer;
            });

            const initFancybox = () => {
                setTimeout(() => {
                    Fancybox.bind("a.lightbox, [data-fancybox]", {
                        l10n: ru
                    });
                }, 500);
            }

            /**
             * Инициализация отображения изображений наименования
             */
            const initPictures = () => {
                pictures.value = initData.pictures;
                if (pictures.value.length) {
                    setMainPicture(pictures.value[0]);
                }
            }

            /**
             * Установка главного изображения
             * @param {Object} picture
             */
            const setMainPicture = picture => {
                mainPicture.src = picture.main;
                mainPicture.link = picture.big;
                initFancybox();
            }

            /**
             * Инициализация торговых педложения
             */
            const initOffers = () => {
                offers.value = initData.offers;

                offers.value.forEach(offer => {
                    // noinspection JSUnresolvedVariable
                    offer.properties.forEach(property => {
                        if (typeof properties[property.code] === 'undefined') {
                            properties[property.code] = {
                                name: property.name,
                                values: {}
                            };

                            // Добавляем в массив выбранных значений новое свойство
                            selected[property.code] = null;

                            // Добавляем в массив порядка следования свойств текущее свойство
                            ordered.properties.push(property.code);
                        }
                        if (typeof properties[property.code].values[property.value] === 'undefined') {
                            properties[property.code].values[property.value] = {
                                active: false,
                                disabled: false
                            };
                        }
                    })
                });

                // Соритровка значений свойства
                for (let [property, propertyObject] of Object.entries(properties)) {
                    let values = Object.keys(propertyObject.values).reduce((values, current) =>
                        values.concat(current), []);

                    ordered.values[property] = values.sort();
                }

                // Выбор первого доступного варианта
                if (ordered.properties.length) {
                    setActive(ordered.values[ordered.properties[0]][0], ordered.properties[0]);
                }
            }

            /**
             * Фильтрация торговых предложений
             */
            const filterOffers = () => {
                // Отключаем все опции у свойств, следующих за текущим
                for (let [property, propertyObject] of Object.entries(properties)) {
                    if (ordered.properties.indexOf(property) > ordered.properties.indexOf(current_property)) {
                        for (let value of Object.values(propertyObject.values)) {
                            value.disabled = true;
                            value.active = false;
                            selected[property] = null;
                        }
                    }
                }

                // Активируем только отфильтрованные
                filtered.value.forEach(offer => {
                    for (let [property, value] of Object.entries(offer.properties)) {
                        properties[property].values[value].disabled = false;
                    }
                });


                // Выбираем первый из доступных вариантов для каждого свойства, где не выбрано значение
                for (let [property, propertyObject] of Object.entries(properties)) {
                    if (!selected[property]) {
                        for (let value of ordered.values[property]) {
                            if (propertyObject.values[value].disabled === false) {
                                return setActive(value, property);
                            }
                        }
                    }
                }
            }

            /**
             * Выбор значения свойства
             *
             * @param {String} value
             * @param {String} code
             */
            const setActive = (value, code) => {
                if (value && !properties[code].values[value].disabled && !properties[code].values[value].active) {
                    // Отмена активности всех элементов в ряду
                    Object.values(properties[code].values).forEach(value => value.active = false);

                    properties[code].values[value].active = true;
                    selected[code] = value;
                    current_property = code;
                }
            }

            /**
             * Добавление товара в корзину
             */
            const addToCart = () => {
                fetch('/catalog/cart/add', {
                    method: 'POST',
                    body: JSON.stringify({
                        item: selectedOffer.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        switch (data.code) {
                            case 0:
                                $.alert({
                                    title: 'Корзина',
                                    content: 'Превышено допустимое к заказу количество товара',
                                    type: 'red',
                                    columnClass: 'medium'
                                });
                                break;
                            case 1:
                                $.confirm({
                                    title: 'Корзина',
                                    content: 'Товар добавлен в корзину',
                                    type: 'blue',
                                    columnClass: 'medium',
                                    buttons: {
                                        yes: {
                                            text: 'Перейти в корзину',
                                            btnClass: 'btn-red',
                                            action: () => location.href = '/catalog/cart'
                                        },
                                        no: {
                                            text: 'Закрыть'
                                        }
                                    }
                                });
                                eventBus.emit('cart-update');
                                break;
                        }

                    });
            }

            /**
             * Добавление товара в список избранного
             */
            const addToFavorites = () => {
                fetch('/catalog/favorites/add/' + selectedOffer.value.item)
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 1) {
                            $.alert({
                                title: 'Избранное',
                                content: 'Товар добавлен в список избранных',
                                type: 'blue',
                                columnClass: 'medium'
                            });
                            eventBus.emit('favorites-update');
                        }
                    });
            }

            watch(selected, filterOffers, { deep: true });

            onMounted(() => {
                initPictures();
                initOffers();
                itemsRelated.value = initData.related;
            });

            return {
                pictures,
                mainPicture,
                offers,
                properties,
                ordered,
                itemsRelated,
                gallery_pictures,
                selectedOffer,
                setMainPicture,
                initOffers,
                setActive,
                addToCart,
                addToFavorites,


                // Работа с отзывами
                feedback,
                feedbackItemsList,
                showFeedbackBlock,
                showFeedbackForm,
                showAllFeedbacks,
                hoverItem,
                resetRate,
                setRate,
                submitFeedback
            }

        }
    }

    createApp(ItemView).mount('#itemViewApp');
});
