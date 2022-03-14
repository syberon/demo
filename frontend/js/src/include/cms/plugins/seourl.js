/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
window.addEventListener('load', function() {
        // Ставим обработчик нажатия на кнопки выбора файла
    [...document.querySelectorAll('button[name="generate-url-button"]')].map(el => {
        el.addEventListener('click', function () {
            let source = document.querySelector(`input[name="${this.dataset['source']}"]`);
            let target = document.querySelector(`input[name="${this.dataset['target']}"]`);
            let formData = new FormData;
            formData.append('source', source.value);
            fetch('/admin/params/system/generateurl', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    target.value = data.url;
                });
        })
    });
});