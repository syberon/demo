/*
 * Copyright (c) 2017.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */
'use strict';

import Sortable from 'sortablejs';

window.addEventListener('load', function() {
    let items = document.querySelector('.slides-sortable');
    if (items) {
        Sortable.create(items, {
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/slider/items/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});