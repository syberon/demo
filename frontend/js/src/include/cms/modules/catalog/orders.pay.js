/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref} from 'vue';

window.addEventListener('load', function () {
    const PayApp = {
        setup() {
            const paymentForm = ref();

            /**
             * Оплата заказа
             */
            const makePayment = () => {
                // noinspection JSUnresolvedFunction
                paymentForm.submit();
            }

            return {
                paymentForm,
                makePayment
            }
        }
    }
    createApp(PayApp).mount('#payApp');
});