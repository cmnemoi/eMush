<template>
    <div class="project-container">
        <ProjectCard :project="project" />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import ProjectCard from "@/components/Game/Terminals/ProjectCard.vue";

export default defineComponent ({
    name: "PilgredTerminal",
    computed: {
        project() {
            return {
                key: "pilgred",
                name: "Pilgred",
                description: "Réparer PILGRED ouvre de nouvelles route, dont celle vers la Terre.",
                efficiency: "Efficacité : 1-1%",
                progress: "0%",
                bonusSkills: [
                    {
                        key: "physicist",
                        name: "Physicien",
                        description: "Incroyable"
                    },
                    {
                        key: "technician",
                        name: "Technicien",
                        description: "Incroyable"
                    }
                ]
            };
        }
    },
    components: {
        ProjectCard
    },
    props: {
    },
    methods: {
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>
.project-container {
    flex-direction: row;
    align-items: stretch;
    padding-bottom: .3em;
    min-height: 276px;
    overflow: auto;

    scroll-snap-type: x mandatory; // scroll will snap to projects

    --scrollbarBG: white;
    --thumbBG: rgba(0, 116, 223, 1);
    --border-radius: 6px;

    scrollbar-width: medium;
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
