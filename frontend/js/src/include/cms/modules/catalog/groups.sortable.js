/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Sortable from 'sortablejs';

window.addEventListener('load', function () {
    Sortable.create(document.querySelector('.items-sortable'), {
        handle: '.handle',
        onEnd: function() {
            let formData = new FormData;
            formData.append('listValues', this.toArray());
            fetch('/catalog/groups/reorder', {
                method: 'POST',
                body: formData
            });
        }
    });
});
