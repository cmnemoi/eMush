<template>
    <ActionPanel
        class="space-battle-panel"
    />
</template>

<script lang="ts">
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import { Equipment } from "@/entities/Equipment";
import { defineComponent } from "vue";
import { Hunter } from "@/entities/Hunter";
import {mapGetters} from "vuex";
import {Item} from "@/entities/Item";

export default defineComponent ({
    components: {
        ActionPanel
    },
    props: {
        target: Equipment
    },
    computed: {
        ...mapGetters('room', [
            'selectedTarget'
        ]),
        getSelectedTarget(): Item | Hunter | null
        {
            if (this.selectedTarget instanceof Hunter) { return this.selectedTarget;}
            return null;
        },
    },
});
</script>

<style lang="scss" scoped>

.equipment-panel {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: 100%;
}

</style>