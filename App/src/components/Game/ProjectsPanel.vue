<template>
    <ul class="research">
        <DaedalusProjectCard v-for="project in projects.researchProjects" :key="project.key" :project="project" />
    </ul>
    <ul class="projects">
        <DaedalusProjectCard v-for="project in projects.neronProjects" :key="project.key" :project="project" />
    </ul>
    <ul class="pilgred" v-if="projects.pilgred">
        <DaedalusProjectCard :project="projects.pilgred" />
    </ul>
</template>

<script lang="ts">
import { PropType, defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import DaedalusProjectCard from "@/components/Game/DaedalusProjectCard.vue";
import { DaedalusProjects } from "@/entities/Daedalus";

export default defineComponent ({
    name: "ProjectsPanel",
    components: {
        DaedalusProjectCard
    },
    props: {
        projects: {
            type: Object as PropType<DaedalusProjects>,
            required: true
        }
    },
    methods: {
        getImgUrl,
        formatText
    }
});
</script>

<style lang="scss" scoped>
.research {
    flex-direction: row;
    flex-wrap: wrap;
    margin-top: 28px;

    li {
        position: relative;
        margin: 0 4px 4px 0;

        span {
            position: absolute;
            z-index: 5;
            margin: 1px 0;
            width: 100%;
            text-align: center;
            font-size: 0.8em;
            text-shadow: 0 0 2px rgba(27, 28, 85, 1), 0 0 2px rgba(27, 28, 85, 1), 0 0 2px rgba(27, 28, 85, 1);
        }
    }
}

.pilgred {
    @extend .research;
    margin-top: 0;
}

.projects {
    @extend .research;
    margin-top: 0;
}
</style>
