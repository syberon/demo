/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import 'include/cms/plugins/seourl';
import 'include/cms/modules/pages/plugin.popup.templates';
import 'include/cms/modules/pages/plugin.popup.pages';
import 'include/cms/modules/imgfilters/plugin.popup.imgfilters';

window.addEventListener('load', function () {
    // Установка сортировки "по возрастанию" при выборе ручной сортировки
    document.querySelector('[name="sort_field"]').onchange = function () {
        if (this.value === 'sort_index') {
            document.querySelector('[name="sort_order"]').value = 'asc';
        }
    }
});