<template>
    <transition name="fade">
        <section class="basic-direction more-projects" v-if="projects.length" ref="listTop">
            <div class="container">
                <div class="row basic-direction-content-wrapper expanding-block-4">
                    <div class="projects-loading-fader" v-if="showLoadingFader"></div>
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
                            v-if="showMoreButton" type="button">Загрузить ещё
                    </button>
                </div>

                <pagination :current-page="params.page"
                            :pages-count="params.pagesCount"
                            @change-page="setPage">
                </pagination>
            </div>
        </section>
    </transition>
</template>

<script>
import {useRoute} from 'vue-router'
import {onMounted, ref, watch} from 'vue';
import ProjectsItem from "./ProjectsItem.vue";
import Pagination from "./Pagination.vue";

export default {
    components: {
        ProjectsItem,
        Pagination
    },

    emits: ['generatePages'],

    setup() {
        /** @type {HTMLElement} listTop */
        const listTop = ref();

        const route = useRoute();
        const projects = ref([]);
        const showMoreButton = ref(false);
        const lastCount = ref(0);

        const showLoadingFader = ref(true);

        let addProjects = false;

        const params = ref({
            pageSize: 20,
            page: 1,
            pagesCount: 0,
            type: ''
        });

        /**
         * Установка текущей страницы
         *
         * @param page
         */
        const setPage = page => {
            params.value.page = page
        }

        /**
         * Загрузка списка проектов
         *
         */
        const getProjectsList = () => {
            lastCount.value = projects.value.length;

            showLoadingFader.value = !addProjects;
            scrollToTop();
            fetch('/projects/getlist', {
                method: 'POST',
                body: JSON.stringify({
                    params: params.value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (addProjects) {
                        data.projects.forEach(element => {
                            projects.value.push(element);
                        })
                        addProjects = false;
                    } else {
                        projects.value = data.projects;
                    }
                    params.value.page = data.pagination.current;
                    params.value.pagesCount = data.pagination.count;
                    saveState();

                    setTimeout(() => {
                        showLoadingFader.value = false;
                        lastCount.value = projects.value.length;
                        // Показ кнопки подгрузки страницы проектов
                        showMoreButton.value = data.count > params.value.pageSize * params.value.page;
                    }, 100);
                });
        };

        /**
         * Сохранение значений полей фильтрации
         */
        const saveState = () => {
            sessionStorage.setItem('portfolio-params', JSON.stringify(params.value));
        }

        /**
         * Восстановление значений полей фильтрации
         */
        const restoreState = () => {
            if (sessionStorage.getItem('portfolio-params')) {
                params.value = JSON.parse(sessionStorage.getItem('portfolio-params'));
            }
            params.value.type = route.params.type;
            getProjectsList();
        }

        /**
         * Подгрузка порции проектов
         */
        const moreButtonClick = () => {
            addProjects = true;
            params.value.page++;
        };

        /**
         * Скроллинг страницы при обновлении
         */
        const scrollToTop = () => {
            if (!addProjects) {
                window.scrollTo(0, listTop.offsetTop);
            }
        }

        watch(() => params.value.page, getProjectsList);

        watch(() => route.params.type, () => {
            params.value.page = 1;
            params.value.type = '';
            saveState();
        })

        onMounted(restoreState);

        return {
            projects,
            params,
            showMoreButton,
            lastCount,
            listTop,
            showLoadingFader,
            moreButtonClick,
            setPage
        }
    }
}
</script>


