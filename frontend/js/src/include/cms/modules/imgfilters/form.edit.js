/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {createApp, ref, reactive, watch, onBeforeMount} from 'vue';

window.addEventListener('load', function () {
    let formData = new FormData(document.querySelector('#imgFiltersApp'));
    const ImgFilters = {
        setup() {
            const inputsActive = {
                thumbnail: ['mode', 'format', 'quality', 'width', 'height'],
                resize: ['format', 'quality', 'width', 'height'],
                relativeresize: ['widen', 'heighten']
            };

            let display = reactive({
                mode: false,
                format: false,
                quality: false,
                width: false,
                height: false,
                widen: false,
                heighten: false
            });

            let type = ref(null);

            /**
             * Сброс видимости полей
             */
            const resetDisplay = () => {
                for (let field of Object.keys(display)) {
                    display[field] = false;
                }
            }

            watch(type, newValue => {
                resetDisplay();
                for (let field of inputsActive[newValue]) {
                    display[field] = true;
                }
            });

            onBeforeMount(() => {
                type.value = formData.get('type');
            })

            return {
                display,
                type
            }
        }
    };

    createApp(ImgFilters).mount('#imgFiltersApp');
});