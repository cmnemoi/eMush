<template>
    <div class="project-container" v-if="terminal">
        <p v-if="terminal.infos.pilgredIsFinished">
            {{ formatText(terminal.infos.pilgredFinishedDescription) }}
        </p>
        <p v-else-if="terminal.infos.noProposedNeronProjects">
            {{ formatText(terminal.infos.noProposedNeronProjectsDescription) }}
        </p>
        <ProjectCard
            v-for="project in terminal.projects"
            :key="project.id"
            :project="project"
            v-else
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import ProjectCard from "@/components/Game/Terminals/ProjectCard.vue";
import { Terminal } from "@/entities/Terminal";

export default defineComponent ({
    name: "ProjectTerminal",
    components: {
        ProjectCard
    },
    props: {
        terminal: {
            type: Terminal,
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
.project-container {
    flex-direction: row;
    align-items: stretch;
    padding-bottom: .3em;
    min-height: 276px;

    scroll-snap-type: x mandatory; // scroll will snap to projects

    --scrollbarBG: white;
    --thumbBG: rgba(0, 116, 223, 1);
    --border-radius: 6px;

    scrollbar-width: thin;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);

    &::-webkit-scrollbar {
        height: 8px;
        border-radius: var(--border-radius);
    }

    &::-webkit-scrollbar-track {
        background: var(--scrollbarBG);
        border-radius: var(--border-radius);
    }

    &::-webkit-scrollbar-thumb {
        background-color: var(--thumbBG);
        border-radius: var(--border-radius);
    }
}
</style>
