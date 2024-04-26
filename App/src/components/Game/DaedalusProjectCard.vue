<template>
    <Tippy tag="li" class="project-card">
        <span v-html="formatText(project.type)"></span>
        <img :src="getProjectImage(project)" :alt="project.name">
        <template #content>
            <h1 v-html="formatText(project.name)"></h1>
            <p v-html="formatText(project.description)"></p>
        </template>
    </Tippy>
</template>

<script lang="ts">
import { PropType, defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";

type DaedalusProject = {
    type: string;
    key: string;
    name: string;
    description: string;
}

export default defineComponent ({
    name: "DaedalusProjectCard",
    props: {
        project: {
            type: Object as PropType<DaedalusProject>,
            required: true
        }
    },
    methods: {
        getProjectImage(project: DaedalusProject): string {
            const folder = project.type === 'Res.' ? 'researches' : 'projects';
            return getImgUrl(`${folder}/${project.key}.png`);
        },
        formatText
    }
});
</script>

<style lang="scss" scoped>
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
</style>
