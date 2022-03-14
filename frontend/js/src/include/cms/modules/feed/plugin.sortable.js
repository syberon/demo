/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Sortable from 'sortablejs';

window.addEventListener('load', function() {
    let items = document.querySelector('.feed-sortable');
    if (items) {
        Sortable.create(items, {
            ghostClass: "ui-state-highlight",
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/feed/items/reorder/' + this.el.dataset.id, {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }

    let attachments = document.querySelector('.pictures-sortable');
    if (attachments) {
        Sortable.create(attachments, {
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/feed/pictures/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});

