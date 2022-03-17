/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import 'vue2-animate/dist/vue2-animate.min.css';
import 'jquery-confirm';
import {reactive, computed, onMounted} from 'vue';

export default function (item_id) {
    const feedback = reactive({
        display: {
            block: false,
            form: false,
            error_message: false,
            success_message: false,
            btn_add_feedback: true
        },

        // Первоначальное количество показываемых отзывов
        show_count: 5,

        active: {
            btn_add_feedback: true
        },

        total_rate: null,
        items: [],
        currentRate: 0,
        rate: [0, 0, 0, 0, 0]
    });

    const feedbackItemsList = computed(() => {
        let itemsList = [];
        let lastIndex = feedback.items.length > feedback.show_count ? feedback.show_count :
            feedback.items.length;

        for (let i = 0; i < lastIndex; i++) {
            itemsList.push(feedback.items[i]);
        }

        return itemsList;
    });

    /**
     * Показ блока отзывов
     */
    const showFeedbackBlock = () => {
        feedback.display.block = true;
        loadFeedbackItems();
    }

    const loadFeedbackItems = () => {
        fetch('/catalog/feedback/getlist/' + item_id)
            .then(response => response.json())
            .then(data => {
                feedback.items = data.items;
                feedback.total_rate = data.total_rate;
            });
    }

    /**
     * Показ формы добавления отзыва
     */
    const showFeedbackForm = () => {
        feedback.display.form = true;
        feedback.active.btn_add_feedback = false;
    }

    const showAllFeedbacks = () => {
        feedback.show_count = feedback.items.length;
    }

    /**
     * Наведение указателя мышь на звезду
     *
     * @param {number} index
     */
    const hoverItem = index => {
        for (let i = 0; i < 5; i++) {
            if (i <= index) {
                feedback.rate[i] = 1;
            } else {
                feedback.rate[i] = 0;
            }
        }
    }

    /**
     * Сброс отображения звезд
     */
    const resetRate = () => {
        for (let i = 0; i < 5; i++) {
            if (i < feedback.currentRate) {
                feedback.rate[i] = 1;
            } else {
                feedback.rate[i] = 0;
            }
        }
    }

    /**
     * Установка текущего выбранного рейтинга
     * @param {number} index
     */
    const setRate = index => {
        feedback.currentRate = index + 1;
    }

    /**
     * Отправка формы отзыва
     */
    const submitFeedback = () => {
        if (!feedback.currentRate) {
            $.alert({
                title: 'Добавление отзыва',
                content: 'Выберите, пожалуйста, оценку товара',
                type: 'red',
                columnClass: 'medium'
            });
        } else {
            let formData = new FormData(document.querySelector('#feedbackForm'));
            fetch('/catalog/feedback/add', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 1) {
                        feedback.display.form = false;
                        setTimeout(() => {
                            feedback.display.success_message = true;
                            feedback.display.error_message = false;

                            loadFeedbackItems();
                        }, 1000);
                    }
                    else {
                        feedback.display.error_message = true;
                    }
                });
        }
    }

    onMounted(loadFeedbackItems);

    return {
        feedback,
        feedbackItemsList,
        showFeedbackBlock,
        showFeedbackForm,
        showAllFeedbacks,
        hoverItem,
        resetRate,
        setRate,
        submitFeedback
    }
};