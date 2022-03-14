/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Sortable from 'sortablejs';

window.addEventListener('load', function () {
    let items = document.querySelector('.faq-sortable');
    if (items) {
        Sortable.create(items, {
            ghostClass: "ui-state-highlight",
            onEnd: function () {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/faq/items/reorder/' + this.el.dataset.id, {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});

