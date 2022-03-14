/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, computed, onMounted} from 'vue';
import 'jquery-confirm';
import FlashMessenger from 'include/cms/plugins/flashmessenger';
import {filterFormValues} from "lib/common";

window.addEventListener('load', function () {
    const OrderEdit = {
        setup() {
            let items = ref([]);
            let order_price = 0;
            let orderId = 0;

            const orderInfo = ref();

            const input = reactive({
                city_name: '',
                zip_code: '',
                delivery: '1',
                payment: '1',
                price_delivery: 0
            });

            const total_price = ref(0);

            const show_delivery_address = computed(() => input.delivery === "1");

            watch(() => input.payment, newValue => {
                    if (newValue === "2") {
                        input.delivery = "2";
                    }
                }
            );

            watch(() => input.delivery, newValue => {
                    if (newValue === "1") {
                        input.payment = "1";
                    }
                }
            );

            watch(items, () => {
                if (total_price.value === 0) {
                    total_price.value = order_price;
                } else {
                    total_price.value = 0;
                    items.value.forEach(item => {
                        // noinspection JSUnresolvedVariable
                        total_price.value += (item.price * item.count);
                    });
                }
            }, { deep: true })

            /**
             * Сохранение изменений в заказе
             */
            const saveChanges = () => {
                let formData = new FormData(document.querySelector('#order-form'));
                formData.append('items', JSON.stringify(items.value));
                formData.append('price', total_price.value);

                fetch(`/catalog/orders/update/${orderId}`, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 1) {
                            FlashMessenger.message('Информация о заказе успешно обновлена');
                        }
                    });
            }

            /**
             * Удаление наименования
             *
             * @param index
             */
            const deleteItem = index => {
                items.value.splice(index, 1);
            }

            /**
             * Добавление наименования
             */
            const addItem = () => {
                $.confirm({
                    title: 'Добавление товара к заказу',
                    content: `
                        <form>
                            <div class="form-group">
                                <label>Введите штрих-код добавляемого товара</label>
                                <input type="text" placeholder="Штрих-код" class="code form-control" required />
                            </div>
                        </form>`,
                    buttons: {
                        formSubmit: {
                            text: 'Добавить',
                            btnClass: 'btn-blue',
                            action: function () {
                                let code = this.$content.find('.code').val();
                                if (!code) {
                                    $.alert('Введите штрих-код');
                                    return false;
                                }

                                fetch(`/catalog/orders/additem/${code}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.code === 1) {
                                            items.value.push(JSON.parse(data.item));
                                        } else {
                                            $.alert('Наименования с данным штрих-кодом не найдено в базе товаров');
                                        }
                                    });
                            }
                        },
                        cancel: {
                            text: 'Отмена',
                        }
                    }
                });
            }

            const getOrderData = (order_id) => {
                fetch('/catalog/orders/getdata/' + order_id)
                    .then(response => response.json())
                    .then(data => {
                        orderId = order_id;
                        items.value = data.order.items;
                        order_price = data.order.price;

                        input.zip_code = data.order.zip_code;
                        input.city_name = data.order.city;
                        input.delivery = data.order.delivery;
                        input.payment = data.order.payment;
                    });
            }

            onMounted(() => getOrderData(orderInfo.value.dataset.order_id));

            return {
                items,
                input,
                total_price,
                show_delivery_address,
                orderInfo,
                saveChanges,
                addItem,
                deleteItem
            }
        },
    }
    filterFormValues('#order-form');
    createApp(OrderEdit).mount('#orderEditApp');
});
