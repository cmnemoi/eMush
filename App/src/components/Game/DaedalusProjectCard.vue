<template>
    <Tippy tag="li" class="project-card">
        <span v-if="displayProjectType" v-html="formatText(project.translatedType)"/>
        <img :src="getProjectImage(project)" :alt="project.name">
        <template #content>
            <h1 v-html="formatText(project.name)"/>
            <p v-if="project.lore" v-html="formatText(project.lore)"/>
            <strong v-if="project.lore">
                <p style="font-weight: bold" v-html="formatText(project.description)"/>
            </strong>
            <p v-else v-html="formatText(project.description)"/>
        </template>
    </Tippy>
</template>

<script lang="ts">
import { PropType, defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { Tippy } from "vue-tippy";
import { DaedalusProject } from "@/entities/Daedalus";

export default defineComponent ({
    name: "DaedalusProjectCard",
    components: { Tippy },
    props: {
        project: {
            type: Object as PropType<DaedalusProject>,
            required: true
        },
        displayProjectType: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        getProjectImage(project: DaedalusProject): string {
            const folder = project.type === 'research' ? 'researches' : 'projects';
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
