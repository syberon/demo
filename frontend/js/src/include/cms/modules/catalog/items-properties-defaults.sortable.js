/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import Sortable from 'sortablejs';

window.addEventListener('load', function () {
    Sortable.create(document.querySelector('.items-sortable'), {
        handle: '.handle',
        onEnd: function() {
            let formData = new FormData;
            formData.append('listValues', this.toArray());
            fetch('/catalog/items-properties-defaults/reorder', {
                method: 'POST',
                body: formData
            });
        }
    });
});
