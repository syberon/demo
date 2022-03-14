/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

window.addEventListener('load', function() {

    // Инициализация слайдера в шапке
    new Swiper ('.top-slider', {
        // Optional parameters
        loop: true,
        centeredSlides: true,
        //effect: 'fade',
        speed: 2000,
        watchOverflow: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: true
        },
        preloadImages: false,
        lazy: {
            loadPrevNext: true
        },

        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        on: {
            slideChange: function() {
                $.each($(this.slides[this.previousIndex]).find('[data-animate-effect]'), function() {
                    let $slide = $(this);
                    $slide.removeClass('animated');
                    $slide.fadeOut(500, function() {
                        $slide.show().addClass('not-animated').removeClass($slide.data('animate-effect'));
                    });
                });

                $.each($(this.slides[this.activeIndex]).find('[data-animate-effect]'), function() {
                    let $slide = $(this);
                    if ($slide.data('animate-delay')) {
                        $slide.css('animation-delay', $slide.data('animate-delay'));
                    }
                    $slide.removeClass('not-animated').addClass('animated').addClass($slide.data('animate-effect'));
                });
            }
        }
    });
});
