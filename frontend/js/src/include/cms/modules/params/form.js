/*
 * Copyright (c) 2016.
 *
 * MtCMS v3.0
 *
 * @author Syber
 */

import {slideUp, slideDown} from "lib/common";
import 'include/cms/plugins/popup.ckfinder';

window.addEventListener('load', function () {
    let transportElement = document.querySelector('[name="transport"]');
    if (transportElement) {
        let smtp = document.querySelector('.smtp-fieldset');
        transportElement.onchange = function () {
            if (this.value === 'Smtp') {
                slideDown(smtp);
            }
            else {
                slideUp(smtp);
            }
        };
        if (transportElement.value === 'Smtp') {
            slideDown(smtp);
        }
    }
});