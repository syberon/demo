/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
import 'jquery-confirm';
import { Fancybox } from "@fancyapps/ui";

export function initForms() {
    [...document.querySelectorAll('.ajax-webform')].map(el => {

        el.addEventListener('submit', function (event) {
            event.preventDefault();
            let form = this;
            let formData = new FormData(form);
            let submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = 'Отправка формы...';
            submitButton.setAttribute('disabled', 'disabled');
            fetch('/admin/webform/processajax/' + form.dataset['id'], {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    submitButton.innerHTML = data.form.submit_text;
                    submitButton.removeAttribute('disabled');
                    switch (data.status) {
                        case 1:
                            Fancybox.close(true);
                            $.alert({
                                title: 'Отправка формы',
                                content: data.form.success_message ? data.form.success_message : 'Ваше сообщение успешно отправлено',
                                type: 'green',
                                columnClass: 'medium'
                            });
                            form.reset();
                            break;
                        case 0:
                            Fancybox.close(true);
                            $.alert({
                                title: 'Отправка формы',
                                content: data.message,
                                type: 'red',
                                columnClass: 'medium'
                            });
                            break;
                    }
                });
        });
    });
}