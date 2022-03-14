/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Sortable from 'sortablejs';

window.addEventListener('load', function() {
    let attachements = document.querySelector('.page-attachments');
    if (attachements) {
        Sortable.create(attachements, {
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());

                fetch('/admin/pages/pictures/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});

