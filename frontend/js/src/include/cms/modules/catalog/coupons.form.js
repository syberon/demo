/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, ref, reactive, watch, onBeforeMount} from 'vue';

window.addEventListener('load', function () {

    let formData = new FormData(document.querySelector('#couponsFormApp'));

    // Генерация информационного окна
    const CouponsForm = {
        setup() {

            const inputsActive = {
                multi: ['count'],
                date: ['date_start', 'date_stop'],
            };

            const display = reactive({
                count: false,
                date_start: false,
                date_stop: false,
            })

            const type = ref(null);

            watch(type, newValue => {
                resetDisplay();
                for (let field of inputsActive[newValue]) {
                    display[field] = true;
                }
            })

            onBeforeMount(() => {
                type.value = formData.get('type');
            })

            /**
             * Сброс видимости полей
             */
            const resetDisplay = () => {
                for (let field of Object.keys(display)) {
                    display[field] = false;
                }
            }

            return {
                type,
                display
            }
        }
    }
    createApp(CouponsForm).mount('#couponsFormApp');
});