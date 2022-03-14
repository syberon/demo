/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, computed, watch, onMounted} from 'vue';
import eventBus from 'include/cms/plugins/vue.eventbus';
import useCoupons from './cart.useCoupons';
import useCdek from 'include/cms/modules/cdek/cart.useCdek';
import autocomplete from "include/cms/modules/cdek/autocomplete.component.vue";
import {filterFormValues} from "lib/common";
import 'jquery-confirm';

window.addEventListener('load', function () {

    const Cart = {
        components: {
            autocomplete
        },

        setup() {
            const items = ref({});
            const discount = ref(0);
            const count = ref(0);
            let updateTimeout = 1000;
            let updateHandler;
            const show_order_form = ref(false);
            const order_button_active = ref(true);

            const city_name = ref();

            const input = reactive({
                city_name: formData.get('delivery-block[city]'),
                city_id: formData.get('delivery-block[city_code]'),
                zip_code: formData.get('delivery-block[zip_code]'),
                delivery: formData.get('delivery'),
                payment: '1',
                price_delivery: 0,
            })

            const show_delivery_address = computed(() => input.delivery === '1');

            const total_price = computed(() => {
                let total_price = 0;
                for (let item of Object.values(items.value)) {
                    total_price += item.price * item.count;
                }
                return total_price;
            });

            const discount_sum = computed(() => {
                return discount.value ? total_price.value - (total_price.value * ((100 - discount.value) / 100)) : 0;
            });

            const {
                use_coupon,
                coupon,
                coupon_discount,
                applyCoupon
            } = useCoupons(total_price, input);

            const {
                success,
                delivery,
                setActiveCity
            } = useCdek(input, total_price, discount_sum, coupon_discount, order_button_active, city_name);

            /**
             * Оформление заказа
             *
             */
            const makeOrder = submitEvent => {
                order_button_active.value = false;
                let orderData = new FormData(submitEvent.target);
                orderData.append('price_delivery', input.price_delivery);
                fetch('/catalog/orders/add', {
                    method: 'POST',
                    body: orderData
                })
                    .then(response => response.json())
                    .then(data => {
                        order_button_active.value = true;
                        if (data.status === 1) {
                            location.href = `/catalog/orders/success/${data.id}`;
                        }
                    });
            }

            /**
             * Инициализация отображения корзины
             */
            const loadList = () => {
                fetch('/catalog/cart/getlist')
                    .then(response => response.json())
                    .then(data => {
                        items.value = data.items;
                        count.value = data.count;
                        // Получение информации о дисконтной скидке
                        if (typeof data.discount !== 'undefined') {
                            discount.value = data.discount;
                        }
                        // Получение информации о примененном купоне
                        if (typeof data.coupon !== 'undefined') {
                            coupon.value = data.coupon;
                        }
                        setTimeout(() => clearTimeout(updateHandler), 100);
                    });
            }

            /**
             * Обновление содержимого корзины
             */
            const updateCart = () => {
                fetch('/catalog/cart/update', {
                    method: 'POST',
                    body: JSON.stringify({
                        items: items.value
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        eventBus.emit('cart-update');
                        loadList();
                        if (typeof data.discount !== 'undefined') {
                            discount.value = data.discount;
                        }
                    });
            }

            /**
             * Удаление товара из корзины
             */
            const deleteItem = token => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить выбранный товар из корзины?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch(`/catalog/cart/delete/${token}`)
                                    .then(response => response.json())
                                    .then(() => {
                                        loadList();
                                        eventBus.emit('cart-update');
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
             * Очистка корзины
             */
            const clearCart = () => {
                $.confirm({
                    title: 'Подтверждение действия',
                    content: 'Удалить все товары из корзины?',
                    type: 'red',
                    columnClass: 'medium',
                    buttons: {
                        yes: {
                            text: 'Да',
                            btnClass: 'btn-red',
                            action: () => {
                                fetch('/catalog/cart/clear')
                                    .then(response => response.json())
                                    .then(() => {
                                        loadList();
                                        eventBus.emit('cart-update');
                                    });
                            }
                        },
                        no: {
                            text: 'Нет'
                        }
                    }
                });
            }


            watch(items, () => {
                clearTimeout(updateHandler);
                updateHandler = setTimeout(updateCart, updateTimeout);
            }, { deep: true });

            watch(() => input.payment, newValue => {
                if (newValue === '2') {
                    input.delivery = '2';
                }
            });

            watch(() => input.delivery, newValue => {
                if (newValue === '2') {
                    // noinspection JSValidateTypes
                    success.value = true;
                    order_button_active.value = true;
                }
            })

            onMounted(() => {
                loadList();
                let hash = document.location.hash;
                if (hash === '#order') {
                    show_order_form.value = true;
                }
            });

            return {
                items,
                discount,
                discount_sum,
                count,
                show_delivery_address,
                show_order_form,
                order_button_active,
                input,
                total_price,
                city_name,
                makeOrder,
                deleteItem,
                clearCart,

                use_coupon,
                coupon,
                coupon_discount,
                applyCoupon,

                success,
                delivery,
                setActiveCity
            }

        },
    }

    let orderForm = document.querySelector('#order-form');
    let formData = new FormData();;
    if (orderForm) {
        formData = new FormData(orderForm);
        filterFormValues('#order-form');
    }
    createApp(Cart).mount('#cartApp');
});