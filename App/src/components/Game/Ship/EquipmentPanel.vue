<template>
    <div class = "equipment-container">
        <div class="name-container">
            <p class="equipment-name">
                {{ getSelectedEquipment.name }}
                <Statuses :statuses="getSelectedEquipment.statuses" type="item" />
            </p>
        </div>
        <ActionPanel
            class="equipment-panel"
        />
    </div>
</template>

<script lang="ts">
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { mapActions, mapGetters } from "vuex";
import { Equipment } from "@/entities/Equipment";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        ActionPanel,
        Statuses
    },
    computed: {
        ...mapGetters('room', [
            'selectedTarget'
        ]),
        getSelectedEquipment(): Equipment
        {
            return this.selectedTarget;
        }
    },
    props: {
        target: Equipment
    }
});
</script>

<style lang="scss" scoped>

.equipment-container {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: 100%;

    .name-container {
        background: #09092d;
        height: 32px;

        .equipment-name {
            text-align: center;
            font-variant: small-caps;
            margin: 0;
            padding: 8px 0;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            display: flex;

            :deep(.status) {
                vertical-align: middle;
                margin-left: 2px;
            }
        }
    }
}

</style>
