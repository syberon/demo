/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, onMounted} from 'vue';
import eventBus from 'include/cms/plugins/vue.eventbus'
import 'jquery-confirm';

window.addEventListener('load', function () {

    const Favorites = {
        setup() {
            const items = ref({});

            /**
             * Инициализация отображения корзины
             */
            const loadList = () => {
                fetch('/catalog/favorites/getlist')
                    .then(response => response.json())
                    .then(data => {
                        items.value = data.items;
                    });
            }

            onMounted(loadList);

            /**
             * Удаление товара из корзины
             *
             * @param {number} id
             */
            const deleteItem = id => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить выбранный товар из избранного?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/catalog/favorites/delete/' + id)
                                    .then(response => response.json())
                                    .then(() => {
                                        loadList();
                                        eventBus.emit('favorites-update');
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
             * Открытие нименования
             *
             * @param {String} link
             */
            const openItem = link => {
                location.href = link;
            }

            /**
             * Очистка корзины
             */
            const clearList = () => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить все товары из избранного?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/catalog/favorites/clear')
                                    .then(response => response.json())
                                    .then(() => {
                                        loadList();
                                        eventBus.emit('favorites-update');
                                    })
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }

            return {
                items,
                openItem,
                clearList,
                deleteItem
            }
        }
    }
    createApp(Favorites).mount('#favoritesApp');
});