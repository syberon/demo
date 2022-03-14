/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

window.addEventListener('load', function () {
    function openCKFinder (field, type) {
        CKFinder.popup({
            height: 600,
            chooseFiles: true,
            resourceType: type,
            onInit: function( finder ) {
                finder.on( 'files:choose', function( evt ) {
                    let file = evt.data.files.first();
                    field.value = file.getUrl();
                } );
            }
        });
    }

    // Ставим обработчик нажатия на кнопки выбора изображения
    [...document.querySelectorAll('.img-select-field')].map(el => {
        el.addEventListener('click', function () {
            openCKFinder(document.querySelector(`input[name="${this.dataset['target']}"]`), 'Images');
        })
    });

    // Ставим обработчик нажатия на кнопки выбора файла
    [...document.querySelectorAll('.file-select-field')].map(el => {
        el.addEventListener('click', function () {
            openCKFinder(document.querySelector(`input[name="${this.dataset['target']}"]`), 'Files');
        })
    });
});