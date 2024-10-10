<template>
    <div
        class="research-info"
        v-for="requirement in terminal.infos.requirements"
    >
        <img :src="getImgUrl('info.png')" alt="info" />
        <p v-html="formatText(requirement)"></p>
    </div>
    <div class="inventory-container">
        <Inventory :items="terminal.items" :minSlot="0" />
    </div>
    <div class="project-container">
        <ResearchCard
            :project="project"
            v-for="project in paginatedProjects"
            :key="project.id"
        />
    </div>
    <div class="pagination-buttons">
        <button @click="prevPage" :disabled="currentPage === 1">
            <img :src="getImgUrl('blue-arrow.png')" alt="arrow-left" />
        </button>
        <span>Page {{ currentPage }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages">
            <img
                :src="getImgUrl('blue-arrow.png')"
                alt="arrow-right"
                class="rotate"
            />
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { Terminal } from "@/entities/Terminal";
import Inventory from "@/components/Game/Inventory.vue";
import ResearchCard from "./ResearchCard.vue";

export default defineComponent({
    name: "ResearchLabTerminal",
    components: {
        ResearchCard,
        Inventory,
    },
    props: {
        terminal: {
            type: Terminal,
            required: true,
        },
    },
    setup(props) {
        const currentPage = ref(1);
        const projectsPerPage = 3;

        const totalPages = computed(() => {
            return Math.ceil(props.terminal.projects.length / projectsPerPage);
        });

        const paginatedProjects = computed(() => {
            const start = (currentPage.value - 1) * projectsPerPage;
            const end = start + projectsPerPage;
            return props.terminal.projects.slice(start, end);
        });

        const nextPage = () => {
            if (currentPage.value < totalPages.value) {
                currentPage.value++;
            }
        };

        const prevPage = () => {
            if (currentPage.value > 1) {
                currentPage.value--;
            }
        };

        return {
            currentPage,
            totalPages,
            paginatedProjects,
            nextPage,
            prevPage,
            getImgUrl,
            formatText,
        };
    },
});
</script>

<style lang="scss">
.research-info {
    box-shadow: 0 1px 1px 0 rgba(9, 10, 97, 0.15);
    align-items: center;
    flex-direction: row;
    border-radius: 2px;
    margin-top: 8px;
    margin-bottom: 8px;
    padding: 0.5em;
    background-color: #e1f7ff;
    p {
        margin: 0;
        margin-left: 0.5em;
    }
    img {
        width: fit-content;
        height: fit-content;
    }
}

.inventory-container {
    margin-bottom: 8px;
    padding: 0.5em 0.25em;
    width: 100%;
    background-color: #9fe8fc;
}

.project-container {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    padding-bottom: 0.3em;
    min-height: 310px;
}

.pagination-buttons {
    button {
        padding: 8px;
    }
    span {
        height: fit-content;
    }
    margin-top: auto;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
}

.terminal div {
    overflow: hidden;
}

.rotate {
    transform: rotate(180deg);
}
</style>

<style lang="scss">
.terminal-container {
    height: 100%;
}
</style>
