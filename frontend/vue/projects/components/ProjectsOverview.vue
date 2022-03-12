<template>
    <section class="portfolio-content" :class="{'portfolio-content--text': lastProject && lastProject.simple === '1'}">
        <div class="container">
            <last-project
                :last-project="lastProject"
                :min-view="minView">
            </last-project>
        </div>
    </section>
    <section class="basic-direction more-projects" v-if="projects.length">
        <div class="container">
            <h2 class="basic-title basic-direction-title">Еще проекты в категории «{{ type.name }}»</h2>
            <div class="row basic-direction-content-wrapper expanding-block-4">
                <projects-item
                    :project="project"
                    :class="{ nextstop: lastCount > 0 && index === lastCount - 1 }"
                    v-for="(project, index) in projects"
                    :key="project.id">
                </projects-item>
            </div>
            <div class="more-link-wrapper">
                <button class="basic-button basic-button--light more-link"
                        @click="moreButtonClick"
                        v-if="showMoreButton" type="button">
                    Загрузить ещё
                </button>
                <router-link :to="{ name: 'projects-list', params: {type: $route.params.type} }"
                             v-if="showAllButton"
                             class="basic-button">
                    Смотреть всё
                </router-link>
            </div>
        </div>
    </section>
</template>

<script>

import 'jquery-mousewheel';
import 'malihu-custom-scrollbar-plugin';
import 'malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css';

import {useRoute, useRouter} from 'vue-router'
import {inject, onMounted, reactive, ref, watch} from 'vue';
import ProjectsItem from "./ProjectsItem.vue";
import LastProject from "./LastProject.vue";

export default {
    components: {
        ProjectsItem,
        LastProject
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const first = inject('first');

        let maxProjectsCount = 8;

        let params = reactive({
            type: '',
            pageSize: 4,
            page: 1,
            filterLast: true
        });

        const showMoreButton = ref(false);
        const showAllButton = ref(false);
        const lastCount = ref(0);

        const type = ref(null);
        const projects = ref([]);
        const lastProject = ref(null);

        const minView = ref(false);

        // Редирект на первый доступный тип проектов, если не выбран
        if (!route.params.type) {
            router.push({
                name: 'projects-overview',
                params: {
                    type: first
                }
            });
        }

        /**
         * Получение последнего проекта в категории
         */
        const getLastProject = () => {
            fetch('/projects/getlast/' + params.type)
                .then(response => response.json())
                .then(data => {

                    lastProject.value = data.project;

                    if (lastProject.value) {
                        setTimeout(() => {
                            if ($(window).width() > 991) {
                                $(".text-content-scroll").mCustomScrollbar({
                                    theme: 'dark',
                                    scrollInertia: "300",
                                });
                            }
                            if (lastProject.value.pictures.length > 1) {
                            }
                        }, 100);
                        minView.value = lastProject.value.pictures.length < 2;
                    }
                });
        }

        /**
         * Получение списка проектов из категории
         */
        const getProjectsList = () => {
            lastCount.value = projects.value.length;
            fetch('/projects/getlist', {
                method: 'POST',
                body: JSON.stringify({
                    params: params
                })
            })
                .then(response => response.json())
                .then(data => {
                    data.projects.forEach(element => {
                        projects.value.push(element);
                    })

                    type.value = data.type;

                    // Показ кнопки подгрузки ряда проектов
                    if (data.count <= params.pageSize) {
                        showMoreButton.value = false;
                    } else if (projects.value.length >= maxProjectsCount) {
                        showMoreButton.value = false;
                    } else showMoreButton.value = projects.value.length !== data.count;

                    // Показ кнопки показа полного списка проектов
                    if (!showMoreButton.value && data.count > projects.value.length) {
                        showAllButton.value = true;
                    }

                    setTimeout(() => {
                        lastCount.value = projects.value.length;
                    }, 100);
                });
        };

        const moreButtonClick = () => {
            params.page++;
        };

        const resetParams = () => {
            lastProject.value = null;
            projects.value = [];
            showAllButton.value = false;
            showMoreButton.value = false;
            sessionStorage.removeItem('portfolio-params');
        };

        watch(() => params.page, getProjectsList);

        watch(() => route.params.type, value => {
            params.type = value;
            resetParams();
            getProjectsList();
            getLastProject();
        });

        /**
         * Загрузка проектов при первом запуске приложения
         */
        onMounted(() => {
            if (route.params.type) {
                params.type = route.params.type;
                resetParams();
                getProjectsList();
                getLastProject();
            }
        })

        return {
            type,
            projects,
            lastProject,
            showMoreButton,
            showAllButton,
            lastCount,
            minView,
            moreButtonClick
        }
    }
}
</script>


