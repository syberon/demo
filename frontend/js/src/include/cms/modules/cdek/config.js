/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import {createApp, onBeforeMount, ref, reactive} from 'vue';
import autocomplete from "include/cms/modules/cdek/autocomplete.component.vue";
import {filterFormValues} from "lib/common";

window.addEventListener('load', function () {
    // Генерация информационного окна

    const CdekConfig = {
        components: {
            autocomplete
        },

        setup() {
            const autocomplete = reactive({
                handler: null,
                timeout: 500,
                items: [],
                selected: true
            });

            const input = reactive({
                city_name: '',
                city_id: ''
            });

            const city_name = ref();

            /**
             * Установка кода выбранного города
             *
             * @param city
             */
            const setActiveCity = city => {
                autocomplete.selected = true;
                autocomplete.items = [];
                input.city_id = city.id;
                input.city_name = city.name;
            }

            onBeforeMount(() => {
                input.city_id = formData.get('sender_city_id');
                input.city_name = formData.get('sender_city_name');
            })

            return {
                input,
                autocomplete,
                city_name,
                setActiveCity,
            }

        },
    }

    let formData = new FormData(document.querySelector('#cdek-config'));
    filterFormValues('#cdek-config');

    createApp(CdekConfig).mount('#cdekConfigApp');
});