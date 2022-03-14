/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {slideToggle} from "lib/common";

window.addEventListener('load', function () {

    [...document.querySelectorAll('.form-toggle')].map(el => {
        el.onclick = function() {
            slideToggle(document.querySelector('#form-' + this.dataset['topic']));
        };
    });
});