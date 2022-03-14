/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

window.addEventListener('load', function() {
    document.querySelector('button[name="get-token-button"]').onclick = function() {
        document.getElementById(this.dataset['target']).value = '';
        document.getElementById("instagram-config").submit();
    };
});