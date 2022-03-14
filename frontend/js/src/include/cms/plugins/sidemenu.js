/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

'use strict';

import Cookies from 'js-cookie';

export function Sidemenu (elementId) {

    let sideMenu;
    let template = '<div class="wrapper"></div><div class="toggler"><span class="fas fa-chevron-left">&nbsp;</span></div>';

    /**
     * Инициализация и построение блока меню
     */
    function init (elementId) {
        sideMenu = document.getElementById(elementId);
        if (sideMenu) {
            let sideMenuContent = sideMenu.innerHTML;
            sideMenu.innerHTML = template;
            sideMenu.querySelector('.wrapper').innerHTML = sideMenuContent;
            sideMenu.querySelector('.toggler').onclick = () => toggle();

            // Показ меню если был открыт ранее
            if (Cookies.get('show-sidebar') === '1') {
                toggle();
            }
            setTimeout(() => {
                sideMenu.style.transition = 'all .5s ease';
            }, 100);
        }

    }

    /**
     * Отображение/сворачивание меню
     *
     */
    function toggle() {
        let style = window.getComputedStyle(sideMenu);
        let toggler = sideMenu.querySelector('.toggler .fas');

        if (style.right === '0px') {
            sideMenu.style.right = `-${style.width}`;
            toggler.classList.remove('fa-chevron-right');
            toggler.classList.add('fa-chevron-left');
            Cookies.set('show-sidebar', 0, { expires: 1, path: "/" });
        }
        else {
            sideMenu.style.right = '0px';
            toggler.classList.remove('fa-chevron-left');
            toggler.classList.add('fa-chevron-right');
            Cookies.set('show-sidebar', 1, { expires: 1, path: "/" });
        }
    }

    if (elementId) {
        init(elementId);
    }
}