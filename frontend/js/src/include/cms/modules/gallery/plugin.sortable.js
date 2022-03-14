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
    let topics = document.querySelector('.topics-sortable');
    if (topics) {
        Sortable.create(topics, {
            draggable: '.category',
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/gallery/topics/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }

    let pictures = document.querySelector('.pictures-sortable');
    if (pictures) {
        Sortable.create(pictures, {
            onEnd: function() {
                let formData = new FormData;
                formData.append('listValues', this.toArray());
                fetch('/admin/gallery/pictures/reorder', {
                    method: 'POST',
                    body: formData
                })
            }
        });
    }
});