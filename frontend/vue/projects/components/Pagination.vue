<template>
    <div class="pages-block" v-if="pagination.pages.length > 1">
        <div class="paginator">
            <nav aria-label="Постраничная навигация">
                <ul class="pagination">
                    <li class="page-item" :class="{ disabled: !pagination.arrows.left }">
                        <a class="page-link" href="Javascript:void(0)" @click="pageDec">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </li>
                    <li class="page-item" v-if="pagination.pages[0] > 1">
                        <a href="Javascript:void(0)" class="page-link" @click="pageSet(1)">1</a> ...
                    </li>
                    <li class="page-item" :class="{ 'active' : page === currentPage}" v-for="page in pagination.pages">
                        <a href="Javascript:void(0)" class="page-link" @click="pageSet(page)">{{ page }} </a>
                    </li>
                    <li class="page-item" v-if="pagination.pages[pagination.pages.length - 1] < pagination.count">
                        ... <a class="page-link" @click="pageSet(pagination.count)"
                               href="Javascript:void(0)">{{ pagination.count }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: !pagination.arrows.right }">
                        <a href="Javascript:void(0)" class="page-link" @click="pageInc">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import {reactive, toRefs, watch} from 'vue';

const Pagination = {
    name: 'Pagination',
    emits: [
        'changePage'
    ],
    props: [
        'currentPage',
        'pagesCount'
    ],
    setup(props, {emit}) {

        const {currentPage, pagesCount} = toRefs(props);

        const pagination = reactive({
            range: 6,
            count: 0,
            pages: [],
            arrows: {
                left: false,
                right: false
            }
        });

        /**
         * Генерация массива листания страниц
         */
        const generatePages = () => {
            pagination.count = pagesCount.value;
            pagination.pages = [];
            pagination.range = 6;

            if (pagination.range > pagesCount.value) {
                pagination.range = pagesCount.value;
            }

            let lowerBound;
            let upperBound;
            let delta = pagination.range / 2;

            if (currentPage.value - delta > pagesCount.value - pagination.range) {
                lowerBound = pagesCount.value - pagination.range + 1;
                upperBound = pagesCount.value;
            } else {
                if (currentPage.value - delta < 0) {
                    delta = currentPage.value;
                }

                let offset = currentPage.value - delta;
                lowerBound = offset + 1;
                upperBound = offset + pagination.range;
            }

            for (let i = lowerBound; i <= upperBound; i++) {
                pagination.pages.push(i);

                // Обработка стрелок навигации
                pagination.arrows.right = !(currentPage.value === pagesCount.value);
                pagination.arrows.left = !(currentPage.value === 1);
            }
        }

        /**
         * Переход на следующую страницу
         */
        const pageInc = () => {
            if (currentPage.value < pagination.pages.length) {
                emit('changePage', currentPage.value + 1);
            }
        }

        /**
         * Переход на предыдущую страницу
         */
        const pageDec = () => {
            if (currentPage.value > 1) {
                emit('changePage', currentPage.value - 1);
            }
        }

        /**
         * Установка выбранной страницы
         */
        const pageSet = page => {
            emit('changePage', page);
        }

        watch(currentPage, generatePages);

        generatePages();

        return {
            pagination,
            currentPage,
            pageInc,
            pageDec,
            pageSet,
        }
    }
}

export default Pagination
</script>


