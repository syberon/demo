<template>
    <div class="col-sm-6 col-md-6 col-xl-3 basic-direction-item">
        <div class="basic-direction-item-wrapper">
            <div class="pic">
                <a :href="project.link" class="stretched-link basic-direction-item-link"></a>
                <div class="image"><img class="img-fluid" :src="project.picture"/></div>
                <div class="fade-layer fade-layer--eye"><img src="/upload/content/images/color-eye.gif"></div>
            </div>
            <div class="content">
                <div class="links">
                    <div class="content">
                        <div class="portfolio-links">
                            <a class="link client-link" :href="project.client.link">{{ project.client.name }}</a>
                        </div>
                        <span class="year-link">{{ project.year }}</span>
                    </div>
                </div>
                <div class="text-content">
                    <a :href="project.link" class="stretched-link basic-direction-item-link"></a>
                    <h5 class="title">
                        <a class="title-link" :href="project.link">{{ project.name }}</a>
                    </h5>
                    <div class="preview" v-html="project.preview"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {toRefs} from "vue";

const ProjectsItem = {
    name: 'ProjectsItem',
    props: {
        'project': Object
    },
    setup(props) {
        const {project} = toRefs(props);

        return {
            project,
        }
    }
}

export default ProjectsItem;
</script>


