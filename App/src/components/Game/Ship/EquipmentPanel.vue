<template>
    <ActionPanel
        class="equipment-panel"
    />
</template>

<script lang="ts">
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import { Equipment } from "@/entities/Equipment";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        ActionPanel
    },
    props: {
        target: Equipment
    }
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
