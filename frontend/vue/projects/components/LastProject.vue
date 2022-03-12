<template>
    <div class="portfolio-item" :class="{'portfolio-item--min': minView}">
        <transition name="fade">
            <div class="row portfolio-item-content" v-if="lastProject">
                <div class="description"
                     :class="
                     {
                         'col-xl-4': lastProject.pictures.length > 1,
                         'col-xxl-3': lastProject.pictures.length > 1,
                         'col-xl-5': lastProject.pictures.length < 2,
                         'col-xxl-4': lastProject.pictures.length < 2
                     }">
                    <div class="project-info">
                        <h2 class="basic-title portfolio-item-title">{{ lastProject.name }}</h2>
                        <div class="links">
                            <div class="content">
                                <div class="portfolio-links">
                                    <a class="link client-link"
                                       :href="lastProject.client.link">
                                        {{ lastProject.client.name }}
                                    </a>
                                </div>
                                <div class="year-link">{{ lastProject.year }}</div>
                            </div>
                        </div>
                        <div class="text text-content-scroll">
                            <div v-html="lastProject.description"></div>
                            <a :href="lastProject.link"
                               v-if="lastProject.link"
                               target="_blank"
                               class="live-link">
                                Посмотреть проект вживую
                            </a>
                        </div>
                    </div>
                    <div class="other-works" v-if="lastProject.other.length">
                        <a class="other-works-title basic-link"
                           :href="lastProject.client.link">
                            Еще работы для клиента {{ lastProject.client.name }}
                            &nbsp;<span><i class="fas fa-arrow-down"></i></span>
                        </a>
                        <div class="content">
                            <a class="other-works-item"
                               v-for="project in lastProject.other"
                               :href="project.link">
                                <img class="img-fluid" :src="project.picture" width="128"/>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="gallery col-xl-7 col-xxl-8" v-if="lastProject.simple === '1'">
                    <div class="content">
                        <div class="text-content" v-html="lastProject.text"></div>
                        <div class="fader-bg">
                            &nbsp;
                        </div>
                    </div>
                </div>
                <div class="gallery"
                     :class="
                     {
                         'col-xl-8': lastProject.pictures.length > 1,
                         'col-xxl-9': lastProject.pictures.length > 1,
                         'col-xl-7 col-xxl-8': lastProject.pictures.length < 2
                     }"
                     v-if="lastProject.pictures.length">

                    <div class="slider__flex">
                        <div class="slider__images slider__images--projectgallery">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide" v-for="picture in lastProject.pictures">
                                        <div class="slider__image"><img alt="" :src="picture.big"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slider__col" v-if="lastProject.pictures.length > 1">
                            <div class="slider__thumbs--projectgallery__prev">
                                <div class="arrow-button">&nbsp;
                                </div>
                            </div>
                            <div class="slider__thumbs slider__thumbs--projectgallery">
                                <div class="swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide" v-for="picture in lastProject.pictures">
                                            <div class="slider__image"><img alt="" :src="picture.small"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="slider__thumbs--projectgallery__next">
                                <div class="arrow-button">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';
import {toRefs} from "vue";

const ProjectsLast = {
    name: 'LastProject',
    props: {
        'lastProject': Object,
        'minView': Boolean
    },
    setup(props) {
        const {lastProject, minView} = toRefs(props);

        const initSliders = () => {
            const sliderProjectGalleryThumbs = new Swiper(".slider__thumbs--projectgallery .swiper", {
                direction: "vertical",
                slidesPerView: 4,
                spaceBetween: 60,
                navigation: {
                    nextEl: ".slider__thumbs--projectgallery__next",
                    prevEl: ".slider__thumbs--projectgallery__prev"
                },
                mousewheel: "true",
                freeMode: "true",
                breakpoints: {
                    0: {
                        direction: "horizontal",
                        spaceBetween: 5,
                        slidesPerView: 5,
                        mousewheel: "false",
                    },
                    575: {
                        spaceBetween: 10,
                        slidesPerView: 4.5,
                        direction: "horizontal",
                    },
                    768: {
                        spaceBetween: 15,
                        slidesPerView: 3.5,
                        direction: "vertical",
                    },
                    992: {
                        spaceBetween: 20,
                        slidesPerView: 3.5,
                        direction: "vertical",
                    },
                    1200: {
                        spaceBetween: 10,
                        slidesPerView: 3.7,
                    },
                    1500: {
                        spaceBetween: 10,
                        slidesPerView: 3.7,
                    },
                    1700: {
                        spaceBetween: 10,
                        slidesPerView: 3.5,
                    },
                    1921: {
                        spaceBetween: 15,
                        slidesPerView: 3.4,
                    }
                }
            });

            new Swiper('.slider__images--projectgallery .swiper', {
                direction: "vertical",
                slidesPerView: 1,
                spaceBetween: 20,
                mousewheel: false,
                navigation: {
                    nextEl: ".slider__next",
                    prevEl: ".slider__prev"
                },
                grabCursor: true,
                thumbs: {
                    swiper: sliderProjectGalleryThumbs
                },
                breakpoints: {
                    0: {
                        direction: "horizontal",
                    },
                    768: {
                        direction: "vertical",
                    }
                }
            });
        };

        initSliders();


        return {
            lastProject,
            minView
        }
    }
}

export default ProjectsLast;
</script>


