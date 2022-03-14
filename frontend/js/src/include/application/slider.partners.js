/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

window.addEventListener('load', function() {

    // Инициализация слайдера партнеров
    new Swiper ('.partners-slider', {
        // Optional parameters
        loop: false,
        speed: 2000,

        slidesPerView: 2,
        spaceBetween: 30,
        watchOverflow: true,

        breakpoints: {
            1200: {
                slidesPerView: 6,
                spaceBetween: 30,
            },
            991: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        },

        autoplay: {
            delay: 5000,
            disableOnInteraction: true
        },
        preloadImages: false,

        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
        },
    });

});
