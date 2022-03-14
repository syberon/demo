<template>
    <draggable v-model="row.cols" item-key="id" :class="['row', 'constructor-row']">
        <template #item="{element}">
            <formcol
                :col="element"
                :fields="fields"
                :structure="structure"
                @edit-column="$emit('editColumn')"
            ></formcol>
        </template>
        <template #footer>
            <div class="buttons row-buttons">
                <a href="Javascript:void(0)" @click="addColumn(row)" class="btn-add" title="Добавить столбец">
                    <i class="fas fa-plus"></i>
                </a>
                <a href="Javascript:void(0)" @click="editRow(row)" class="btn-edit" title="Редактировать ряд">
                    <i class="fas fa-pen"></i>
                </a>
                <a href="Javascript:void(0)" @click="removeRow(index)" title="Удалить ряд" class="btn-delete">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </template>
    </draggable>
</template>

<script>
import {dialogAddColumn, dialogAddRow} from '../templates/dialogs';
import {populateFieldsSelect} from "../common/populate-fields";
import {toRefs} from 'vue';

const formRow = {
    name: 'formrow',
    emits: ['editColumn'],
    props: ['row', 'fields', 'index', 'structure'],
    setup(props) {
        const {row, fields, index, structure} = toRefs(props);

        /**
         * Добавление нового столбца в ряд
         *
         * @param row
         */
        const addColumn = row => {
            $.confirm({
                title: 'Добавление нового столбца',
                content: dialogAddColumn,
                columnClass: 'small',
                type: 'blue',
                buttons: {
                    formSubmit: {
                        text: 'Добавить',
                        btnClass: 'btn-blue',
                        action: function () {
                            row.cols.push({
                                id: this.$content.find('[name="id"]').val(),
                                class: this.$content.find('[name="class"]').val(),
                                content: this.$content.find('[name="content"]').val(),
                                field: this.$content.find('[name="field"]').val(),
                            });
                        }
                    },
                    cancel: {
                        text: 'Отмена',
                    }
                },

                onContentReady: function () {
                    populateFieldsSelect(this.$content.find('[name="field"]'), fields);
                }
            });
        }

        /**
         * Редактирование параметров ряда
         *
         * @param row
         */
        const editRow = row => {
            $.confirm({
                title: 'Редактирование ряда',
                content: dialogAddRow,
                columnClass: 'small',
                type: 'blue',
                buttons: {
                    formSubmit: {
                        text: 'Сохранить',
                        btnClass: 'btn-blue',
                        action: function () {
                            row.id = this.$content.find('#id').val();
                            row.class = this.$content.find('#class').val();
                        }
                    },
                    cancel: {
                        text: 'Отмена',
                    }
                },

                onContentReady: function () {
                    this.$content.find('#id').val(row.id);
                    this.$content.find('#class').val(row.class);
                }
            });
        }

        /**
         * Удаление ряда
         *
         * @param index
         */
        const removeRow = index => {
            $.confirm({
                title: 'Подтверждение действия',
                content: 'Удалить выбранный ряд?',
                columnClass: 'small',
                type: 'red',
                buttons: {
                    yes: {
                        text: 'Да',
                        btnClass: 'btn-red',
                        action: () => {
                            structure.value.rows.splice(index, 1);
                        }
                    },
                    no: {
                        text: 'Нет'
                    }
                }
            });
        }

        return {
            row,
            fields,
            structure,
            index,
            addColumn,
            editRow,
            removeRow
        }
    }
}

export default formRow;
</script>