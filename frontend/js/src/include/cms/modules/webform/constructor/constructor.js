/*
 * Copyright (c) 2021.
 *
 * @author Syber
 */
'use strict';

import 'jquery-confirm';
import {createApp, ref, onMounted, watch, toRefs} from 'vue';
import formRow from './components/formrow.vue';
import formCol from './components/formcol.vue'
import {dialogAddRow} from './templates/dialogs';
import draggable from 'vuedraggable'
import FlashMessenger from 'include/cms/plugins/flashmessenger';

window.addEventListener('load', () => {
    const WebformConstructor = {
        props: [
            'form_id'
        ],
        setup(props) {
            const {form_id} = toRefs(props);

            /** @type {Object} form */
            let form = null;

            const fields = ref(null);
            const structure = ref({
                rows: []
            })

            const initButtons = () => {
                // noinspection JSUnresolvedVariable
                if (form.use_captcha === '1') {
                    fields.value['captcha'] = {
                        label: 'ReCaptcha',
                        name: 'captcha',
                        type: 'captcha',
                    };
                }

                // noinspection JSUnresolvedVariable
                fields.value['submit'] = {
                    label: form.submit_text ? form.submit_text : 'Отправка',
                    name: 'submit',
                    type: 'button',
                };
            }

            /**
             * Проверка существующей структуры на корректность состава полей
             */
            const checkStructure = () => {
                structure.value.rows.forEach(
                    /** @param {object} row */
                    row => {
                    if (row.cols.length) {
                        row.cols.forEach(col => {
                            if (col.field) {
                                if (!fieldExist(col.field)) {
                                    col.field = null;
                                }
                            }
                        })
                    }
                })
            }

            /**
             * Сброс маркировки использованных полей
             */
            const resetUsed = () => {
                for (let field in fields.value) {
                    if (fields.value.hasOwnProperty(field)) {
                        fields.value[field].used = false;
                    }
                }
            }

            /**
             * Проверка и отметка уже использованных полей
             */
            const markUsed = () => {
                initButtons();
                resetUsed();
                checkStructure();
                structure.value.rows.forEach(
                    /** @param {object} row */
                    row => {
                    row.cols.forEach(col => {
                        if (col.field) {
                            fields.value[col.field].used = true;
                        }
                    })
                });
            }

            /**
             * Проверка на существование поля в списке доступных
             *
             * @param name
             */
            const fieldExist = (name) => {
                return typeof (fields.value[name]) !== 'undefined';

            }

            /**
             * Сохранение структуры
             */
            const saveStructure = () => {
                fetch(`/admin/webform/constructor/${form_id.value}`, {
                    method: 'POST',
                    body: JSON.stringify(structure.value)
                })
                    .then(response => response.json())
                    .then(() => {
                        FlashMessenger.message('Структура успешно сохранена');
                    });
            }

            /**
             * Добавление нового ряда
             */
            const addRow = () => {
                $.confirm({
                    title: 'Добавление нового ряда',
                    content: dialogAddRow,
                    columnClass: 'small',
                    type: 'blue',
                    buttons: {
                        formSubmit: {
                            text: 'Добавить',
                            btnClass: 'btn-blue',
                            action: function () {
                                structure.value.rows.push({
                                    id: this.$content.find('#id').val(),
                                    class: this.$content.find('#class').val(),
                                    cols: []
                                });
                            }
                        },
                        cancel: {
                            text: 'Отмена',
                        }
                    }
                });
            }


            watch(structure, markUsed, { deep: true });

            onMounted(() => {
                fetch('/admin/webform/getformdata/' + form_id.value)
                    .then(response => response.json())
                    .then(data => {
                        form = data.form;
                        fields.value = data.fields;
                        if (form.structure) {
                            structure.value = JSON.parse(form.structure);
                        }
                    })
            })

            return {
                fields,
                structure,
                addRow,
                saveStructure,
                markUsed
            }
        },
    }

    /** @type {HTMLElement} mountEl */
    const mountEl = document.querySelector('#webformConstructor');
    let app = createApp(WebformConstructor, { ...mountEl.dataset });
    app.component('draggable', draggable);
    app.component('formrow', formRow);
    app.component('formcol', formCol);
    app.mount(mountEl);
});

