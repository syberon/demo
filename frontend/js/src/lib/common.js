/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

/**
 * Плавное скрывание элемента
 *
 * @param element
 */
export function fadeOut(element) {
    let fadeTarget;
    if (typeof element == 'string') {
        fadeTarget = document.querySelector(element);
    }
    else if (typeof element == 'object') {
        fadeTarget = element;
    }
    if (fadeTarget) {
        let fadeEffect = setInterval(function () {
            if (!fadeTarget.style.opacity) {
                fadeTarget.style.opacity = 1;
            }
            if (fadeTarget.style.opacity > 0) {
                fadeTarget.style.opacity -= 0.05;
            } else {
                fadeTarget.style.display = 'none';
                clearInterval(fadeEffect);
            }
        }, 10);
    }
}

/**
 * Плавный показ элемента
 *
 * @param element
 */
export function fadeIn(element) {
    let fadeTarget;
    if (typeof element == 'string') {
        fadeTarget = document.querySelector(element);
    }
    else if (typeof element == 'object') {
        fadeTarget = element;
    }
    if (fadeTarget) {
        let fadeEffect = setInterval(function () {
            if (!fadeTarget.style.opacity || fadeTarget.style.opacity === '0') {
                fadeTarget.style.opacity = 0;
                fadeTarget.style.display = 'block';
            }
            if (fadeTarget.style.opacity < 1) {
                fadeTarget.style.opacity = +fadeTarget.style.opacity + 0.05;
            } else {
                clearInterval(fadeEffect);
            }
        }, 10);
    }
}

/**
 * Преобразование текстового контента в DOM Node
 * @param htmlString
 */
export function createElementFromHTML(htmlString) {
    let div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}

/**
 * Плавное раскрытие/сворачивание элемента вверх
 *
 * @param target
 * @param duration
 * @param callback
 */
export function slideUp(target, duration= 500, callback) {
    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.boxSizing = 'border-box';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout( () => {
        target.style.display = 'none';
        target.style.removeProperty('height');
        target.style.removeProperty('padding-top');
        target.style.removeProperty('padding-bottom');
        target.style.removeProperty('margin-top');
        target.style.removeProperty('margin-bottom');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        if (callback) {
            callback();
        }
    }, duration);
}

/**
 * Плавное раскрытие/сворачивание элемента вниз
 *
 * @param target
 * @param duration
 * @param callback
 */
export function slideDown(target, duration= 500, callback) {
    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;

    if (display === 'none')
        display = 'block';

    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    window.setTimeout( () => {
        target.style.removeProperty('height');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        if (callback) {
            callback();
        }
    }, duration);
}

/**
 * Плавное раскрытие/сворачивание элемента
 *
 * @param target
 * @param duration
 * @param callback
 */
export function slideToggle(target, duration = 500, callback) {
    if (window.getComputedStyle(target).display === 'none') {
        return slideDown(target, duration, callback);
    } else {
        return slideUp(target, duration, callback);
    }
}


/**
 * Фильтрация атрибутов формы для Vue
 *
 * @param form_id
 */
export function filterFormValues(form_id) {
    let form = document.querySelector(form_id);
    let formData = new FormData(form);

    for (let key of formData.keys()) {
        let el = form.querySelector('[name="' + key + '"]');
        if (el.hasAttribute('v-model')) {
            el.removeAttribute('value');
        }
    }
}

