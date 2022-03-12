/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */
'use strict';

import {createApp, provide, toRefs} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';
import ProjectsOverview from './components/ProjectsOverview.vue';
import ProjectsList from './components/ProjectsList.vue';
import Portfolio from './components/Portfolio.vue';

window.addEventListener('load', () => {

    /**
     * Параметры роутера
     */
    const routes = [
        {
            name: 'portfolio',
            path: '/portfolio/:type?',
            component: Portfolio,
            children: [
                {
                    name: 'projects-overview',
                    path: '',
                    component: ProjectsOverview
                },
                {
                    name: 'projects-list',
                    path: 'list',
                    component: ProjectsList
                }
            ]
        }
    ]

    const router = createRouter({
        history: createWebHistory(),
        routes,
    })

    /**
     * Приложение "портфолио"
     */
    const PortfolioApp = {
        props: [
            'first'
        ],

        setup(props) {
            const {first} = toRefs(props);
            provide('first', first.value);
        }
    }

    const mountEl = document.querySelector('#portfolioApp');
    let app = createApp(PortfolioApp, {...mountEl.dataset});
    app.use(router);
    app.mount(mountEl);
});