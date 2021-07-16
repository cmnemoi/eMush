<template>
    <span
        v-for="(status, key) in statuses"
        :key="key"
        class="status"
    >
        <Tooltip>
            <template #tooltip-trigger>
                <img :src="statusIcon(status)">
                <span v-if="status.charge">{{ status.charge }}</span>
            </template>
            <template #tooltip-content="{ formatContent }">
                <h1 v-html="formatContent(status.name)" />
                <p v-html="formatContent(status.description)" />
            </template>
        </Tooltip>
    </span>
</template>

<script lang="ts">
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum } from "@/enums/status.item.enum";
import Tooltip from "@/components/Utils/ToolTip.vue";
import { defineComponent } from "vue";
import { Status } from "@/entities/Status";

export default defineComponent ({
    components: { Tooltip },
    props: {
        statuses: Array,
        type: String
    },
    computed: {
        statusIcon() {
            return (status: Status): string|null => {
                switch (this.type) {
                case "player":
                    return statusPlayerEnum[status.key]?.icon || null;
                case "disease":
                    return require('@/assets/images/status/disease.png');
                case "item":
                case "equipment":
                    return statusItemEnum[status.key]?.icon || null;
                case "room":
                    return require('@/assets/images/status/fire.png');
                default:
                    return null;
                }
            };
        }
    }
});
</script>
