<!--suppress JSUnresolvedVariable -->
<template>
    <div class="city-autocomplete" v-if="autocomplete.items.length > 1">
        <ul>
            <li v-for="item in autocomplete.items" @click="setActive(item)">{{ item.full }}</li>
        </ul>
    </div>
</template>

<script>
import {reactive, watch, toRefs} from "vue";

const autoComplete = {
    name: 'autocomplete',
    emits: ['setActiveCity'],
    props: ['input', 'city_name'],

    setup(props, {emit}) {
        const autocomplete = reactive({
            handler: null,
            timeout: 200,
            items: [],
            selected: true
        });

        const {input, city_name} = toRefs(props);

        const setActive = (item) => {
            emit('setActiveCity', item);
            autocomplete.items = [];
            autocomplete.selected = true;
        }

        /**
         * Получение информации для автокомплита
         */
        const getAutocompleteValues = () => {
            autocomplete.items = [];
            let formData = new FormData;
            formData.append('city', input.value.city_name);
            fetch('/admin/cdek/get-autocomplete', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.cities.geonames !== undefined) {
                        if (data.cities.geonames.length > 1) {
                            for (let city of data.cities.geonames) {
                                autocomplete.items.push({
                                    id: city.id,
                                    name: city.cityName,
                                    full: city.name,
                                    post: city.postCodeArray
                                })
                            }
                        } else if (data.cities.geonames.length === 1) {
                            let city = data.cities.geonames.pop();
                            emit('setActiveCity', {
                                id: city.id,
                                name: city.cityName,
                                post: city.postCodeArray
                            })
                        }
                    }
                });
        }

        // Обработка автокомплита при заполнении поля города
        watch(() => input.value.city_name, newValue => {
            if (!autocomplete.selected) {
                input.value.city_id = null;
                city_name.value.classList.remove('is-valid');
                if (newValue.length > 2) {
                    clearTimeout(autocomplete.handler);
                    autocomplete.handler = setTimeout(getAutocompleteValues, autocomplete.timeout);
                } else {
                    autocomplete.items = [];
                }
            } else {
                autocomplete.selected = false;
            }
        });

        return {
            autocomplete,
            setActive
        }


    }
}

export default autoComplete;
</script>
