/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {slideToggle} from "lib/common";

window.addEventListener('load', function() {
    document.querySelector('.advanced-button').onclick = () => {
        slideToggle(document.querySelector('#advanced-fieldset'));
    }
});