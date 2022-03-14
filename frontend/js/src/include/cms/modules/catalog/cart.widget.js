/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, onMounted, ref} from 'vue';
import eventBus from 'include/cms/plugins/vue.eventbus';

window.addEventListener('load', function () {
    const CartWidget = {
        setup() {
            const items = ref(0);
            const price = ref(0);
            const favorites = ref(0);

            onMounted(() => {
                updateCartInformation();
                updateFavoritesInformation();
            })

            /**
             * Обновление информации о корзине
             */
            const updateCartInformation = () => {
                fetch('/catalog/cart/getstatus')
                    .then(response => response.json())
                    .then(data => {
                        items.value = data.items;
                        price.value = data.price;
                    });
            }

            /**
             * Обновление информации о корзине
             */
            const updateFavoritesInformation = () => {
                fetch('/catalog/favorites/getstatus')
                    .then(response => response.json())
                    .then(data => {
                        favorites.value = data.items;
                    });
            }

            eventBus.on('cart-update', updateCartInformation);
            eventBus.on('favorites-update', updateFavoritesInformation);

            return {
                items,
                price,
                favorites
            }
        }
    }
    createApp(CartWidget).mount('#cartWidget');
});