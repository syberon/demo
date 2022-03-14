/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Sortable from 'sortablejs';

window.addEventListener('load', function() {
    let items = document.querySelector('.blocks-sortable');
    if (items) {
        Sortable.create(items, {
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/landing/blocks/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});