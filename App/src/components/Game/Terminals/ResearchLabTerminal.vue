<template>
    <div
        class="research-info"
        v-for="requirement in terminal.infos.requirements"
        :key="requirement"
    >
        <img :src="getImgUrl('info.png')" alt="info" />
        <p v-html="formatText(requirement)"></p>
    </div>
    <div class="inventory-container">
        <Inventory
            :items="terminal.items"
            :min-slot="0"
            :selected-item="selectedItem"
            @select="toggleItemSelection"
        />
    </div>
    <div class="project-container">
        <ResearchCard
            :project="project"
            v-for="project in terminal.projects"
            :key="project.id"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { Terminal } from "@/entities/Terminal";
import { Item } from "@/entities/Item";
import Inventory from "@/components/Game/Inventory.vue";
import ResearchCard from "./ResearchCard.vue";

export default defineComponent({
    name: "ResearchLabTerminal",
    components: {
        ResearchCard,
        Inventory
    },
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
    },
    computed: {
        ...mapGetters('player', [
            'selectedItem'
        ])
    },
    methods: {
        ...mapActions({
            'selectTarget': 'player/selectTarget'
        }),
        // Route selection to the player's actions-card, like selecting an item in the inventory
        toggleItemSelection(target: Item | null): void {
            if (this.selectedItem === target) {
                this.selectTarget({ target: null });
            } else {
                this.selectTarget({ target: target });
            }
        }
    },
    setup() {
        return {
            getImgUrl,
            formatText
        };
    }
});
</script>

<style scoped lang="scss">
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

    // Restore the inventory's horizontal scroll, otherwise clipped by
    // the `.terminal div { overflow: hidden }` rule below when there are many items.
    :deep(.inventory) {
        overflow-x: auto;
    }
}

.project-container {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    padding-bottom: 0.3em;
    min-height: 310px;
    overflow-x: auto !important;
    scrollbar-color: #9fe8fc;
}

.terminal div {
    overflow: hidden;
}

.rotate {
    transform: rotate(180deg);
}

.terminal-container {
    height: 100%;
}
</style>
