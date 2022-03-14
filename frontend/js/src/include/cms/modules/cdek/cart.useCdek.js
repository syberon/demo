// noinspection JSUnresolvedVariable

/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {ref, reactive, watch, onMounted} from 'vue';

export default function (input, total_price, discount_sum, coupon_discount, order_button_active, city_name) {

    const success = ref(false);

    const delivery = reactive({
        min: 0,
        max: 0,
        selfpickup: false
    });

    /**
     * Установка кода выбранного города
     *
     * @param {Object} city
     */
    const setActiveCity = city => {
        input.city_id = city.id;
        input.city_name = city.name;
        input.zip_code = city.post.shift();
        city_name.value.classList.add('is-valid');
    }

    /**
     * Расчет стоимости доставки СДЭК
     */
    const calculatePrice = () => {

        let totalPrice = total_price.value;

        if (typeof discount_sum !== 'undefined') {
            totalPrice -= discount_sum.value;
        }
        if (typeof coupon_discount !== 'undefined') {
            totalPrice -= coupon_discount.value;
        }
        let formData = new FormData;
        formData.append('city_id', input.city_id);
        formData.append('zip_code', input.zip_code);
        formData.append('total_price', totalPrice);

        fetch('/admin/cdek/calculate', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                switch (data.status) {
                    case 1:
                        // Расчет произведен успешно
                        delivery.min = data.min;
                        delivery.max = data.max;
                        success.value = true;
                        order_button_active.value = true;
                        break;
                    case 2:
                        // Город доставки совпадает с городом отправления
                        success.value = false;
                        break;
                }
                delivery.selfpickup = data.selfpickup;
                input.price_delivery = data.price;
            });
    }

    // Расчет стоимости доставки если задан правильный город
    watch(() => input.city_id, newValue => {
        if (newValue) {
            calculatePrice();
        }
    });

    watch(() => input.delivery, newValue => {
        if (newValue === '1') {
            success.value = false;
            order_button_active.value = false;
            calculatePrice();
        }
    });

    onMounted(() => {
        // Автоматически провести расчет, если город уже задан
        if (input.city_id && input.delivery === '2') {
            calculatePrice();
        } else {
            // Поиск идентификатора города, если задано только название или почтовый индекс
            if (input.city_name || input.zip_code) {
                let formData = new FormData;
                formData.append('city_name', input.city_name);
                formData.append('zip_code', input.zip_code);
                fetch('/admin/cdek/find-city', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 1) {
                            input.city_id = data.city_id;
                        }
                    });
            }
        }
    });

    return {
        success,
        delivery,
        setActiveCity
    }
};