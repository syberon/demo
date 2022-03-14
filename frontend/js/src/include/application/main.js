/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import WOW from 'wow.js';

window.addEventListener('load', function() {
    // инициализация WOW-анимации
    new WOW({
        offset: 100,
        mobile: false,
    }).init();
});
