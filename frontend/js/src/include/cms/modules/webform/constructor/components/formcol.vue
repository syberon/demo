<template>
    <div :class="[col.class, 'constructor-col']">
        <div class="info">
            <template v-if="col.class">
                <span class="name">class: </span> <span class="value">{{ col.class }}</span>
            </template>
            <template v-if="col.id">id: {{ col.id }}</template>
        </div>
        <div class="field-block">
            <div v-if="col.field" class="field">
                <div class="field-label">{{ fields[col.field].label }}</div>
                <div class="buttons field-buttons">
                    <a href="Javascript:void(0)" @click="removeField(col)" class="btn-delete" title="Удалить поле">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
            <div v-else-if="col.content" class="content" v-html="nl2br(col.content)"></div>
            <div class="col-buttons buttons">
                <a href="Javascript:void(0)" @click="editColumn(col)" class="btn-edit" title="Редактировать столбец">
                    <i class="fas fa-pen"></i>
                </a>
                <a href="Javascript:void(0)" @click="removeColumn(col)" class="btn-delete" title="Удалить столбец">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</template>

<script>

import {dialogAddColumn} from '../templates/dialogs';
import {populateFieldsSelect} from "../common/populate-fields";
import {toRefs} from 'vue';

const formCol = {
    name: 'formcol',
    emits: ['editColumn'],
    props: ['col', 'fields', 'structure'],

    setup(props, {emit}) {

        const {col, fields, structure} = toRefs(props);

        const nl2br = (str) => {
            return str.replace(/\r\n|\r|\n/g, '<br />');
        }

        /**
         * Удаление установленного поля из столбца
         *
         * @param column
         */
        const removeField = column => {
            $.confirm({
                title: 'Подтверждение действия',
                content: 'Удалить поле формы из столбца?',
                type: 'red',
                columnClass: 'small',
                buttons: {
                    yes: {
                        text: 'Да',
                        btnClass: 'btn-red',
                        action: () => {
                            column.field = null;
                        }
                    },
                    no: {
                        text: 'Нет'
                    }
                }
            });
        }

        /**
         * Удаление столбца из ряда
         *
         * @param column
         */
        const removeColumn = column => {
            $.confirm({
                title: 'Подтверждение действия',
                content: 'Удалить выбранный столбец?',
                type: 'red',
                columnClass: 'small',
                buttons: {
                    yes: {
                        text: 'Да',
                        btnClass: 'btn-red',
                        action: () => {
                            structure.value.rows.forEach(row => {
                                row.cols.forEach((col, col_index) => {
                                    if (col.field === column.field) {
                                        row.cols.splice(col_index, 1);
                                    }
                                })
                            })
                        }
                    },
                    no: {
                        text: 'Нет'
                    }
                }
            });
        }

        /**
         * Редактирование параметров столбца
         *
         * @param column
         */
        const editColumn = column => {
            if (column.field) {
                fields.value[column.field].used = false;
            }
            $.confirm({
                title: 'Редактирование столбца',
                content: dialogAddColumn,
                columnClass: 'small',
                type: 'blue',
                buttons: {
                    formSubmit: {
                        text: 'Сохранить',
                        btnClass: 'btn-blue',
                        action: function () {
                            column.id = this.$content.find('[name="id"]').val();
                            column.class = this.$content.find('[name="class"]').val();
                            column.content = this.$content.find('[name="content"]').val();
                            column.field = this.$content.find('[name="field"]').val();
                            emit('editColumn');
                        }
                    },
                    cancel: {
                        text: 'Отмена',
                        action: () => {
                            emit('editColumn');
                        }
                    }
                },

                onContentReady: function () {
                    populateFieldsSelect(this.$content.find('[name="field"]'), fields);
                    this.$content.find('[name="id"]').val(column.id);
                    this.$content.find('[name="class"]').val(column.class);
                    this.$content.find('[name="content"]').val(column.content);
                    this.$content.find('[name="field"]').val(column.field);
                }
            });
        }

        return {
            col,
            fields,
            structure,
            nl2br,
            removeField,
            removeColumn,
            editColumn,
        }

    }
}

export default formCol;

</script>