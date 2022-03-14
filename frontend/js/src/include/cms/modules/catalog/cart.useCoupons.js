/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import FlashMessenger from 'include/cms/plugins/flashmessenger';
import {ref, computed} from 'vue';

export default function (total_price, input) {
    const use_coupon = ref(false);
    const coupon = ref();

    input.coupon = '';

    const coupon_discount = computed(() => {
        /** @namespace this.coupon.discount_type **/
        if (coupon.value) {
            switch (coupon.value.discount_type) {
                case 'sum':
                    return coupon.value.discount;
                case 'percent': {
                    return total_price.value - total_price.value * ((100 - coupon.value.discount) / 100);
                }
            }
        }
        else {
            return 0;
        }
    });

    /**
     * Применение кода купона
     */
    const applyCoupon = () => {
        if (input.coupon) {
            let formData = new FormData;
            formData.append('coupon', input.coupon);
            fetch('/catalog/cart/applycoupon', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.code === 1) {
                        FlashMessenger.message('Купон успешно применен');
                        coupon.value = data.coupon;
                    }
                    else {
                        FlashMessenger.message(data.message, 'error');
                    }
                });
        }
    }

    return {
        use_coupon,
        coupon,
        coupon_discount,
        applyCoupon
    }
};