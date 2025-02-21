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
    <div class="actions" v-show="!terminal.infos.noProposedNeronProjects && !terminal.infos.pilgredIsFinished">
        <Tippy
            tag="button"
            class="icon share"
            @click="shareProjects(terminal.projects)">
            <template #content>
                <h1 v-html="formatText(terminal.buttons.shareProjects.name)" />
                <p v-html="formatText(terminal.buttons.shareProjects.description)" />
            </template>
            <img :src="getImgUrl('comms/wall.png')">
        </Tippy>
    </div>
</template>

<script lang="ts">
import { Project } from "@/entities/Project";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { mapActions, mapGetters } from "vuex";
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
    computed: {
        ...mapGetters({
            'typedMessage': 'communication/typedMessage'
        })
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'updateTypedMessage': 'communication/updateTypedMessage'
        }),
        getImgUrl,
        formatText,
        shareProjects(projects: Project[]) {
            const publicChannelTab = document.getElementsByClassName('tabs')[0].getElementsByClassName('public')[0] as HTMLDivElement;
            publicChannelTab.click();
            const projectList = projects.join("\n");
            let icon = "";
            switch(this.terminal.key){
            case "neron_core":
            case "auxiliary_terminal":
                icon = ":neron:";
                break;
            case "pilgred":
                icon = ":pa_pilgred:";
                break;
            default:
                icon = "invalid terminal type";
            }
            if (this.typedMessage) {
                this.updateTypedMessage(`${this.typedMessage}\n\n${icon} **${this.terminal.name}**\n${projectList}`);
            } else {
                this.updateTypedMessage(`${icon} **${this.terminal.name}**\n${projectList}`);
            }
        }
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
.actions {
        flex-direction: row;
        justify-content: right;

        button {
            @include button-style;
            margin: .2rem;

            &.icon { padding: 1px 4px; }
        }
    }
</style>
