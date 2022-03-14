/*
 * Copyright (c) 2021.
 *
 * @author Syber
 */

/**
 * Заполнение поля ввода значениями полей
 *
 * @param select
 * @param fields
 */
const populateFieldsSelect = (select, fields) => {
    select.append(`<option value=""></option>`);
    for (let field of Object.keys(fields.value)) {
        if (!fields.value[field].used) {
            select.append(`<option value="${field}">${fields.value[field].label}</option>`);
        }
    }
}

export {populateFieldsSelect}