<template>
    <span
        v-for="(status, key) in statuses"
        :key="key"
        class="status"
    >
        <Tooltip>
            <template v-slot:tooltip-trigger><img :src="statusIcon(status)">
            <span v-if="status.charge">{{ status.charge }}</span></template>
            <template v-slot:tooltip-content><h1>{{ status.name }}</h1>
            <p>{{ status.description }}</p>
            </template>
        </Tooltip>
    </span>
</template>

<script>
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum } from "@/enums/status.item.enum";
import Tooltip from "@/components/Utils/ToolTip";

export default {
    components: {Tooltip},
    props: {
        statuses: Array,
        type: String
    },
    computed: {
        statusIcon() {
            return (status) => {
                switch (this.type) {
                case "player":
                    return statusPlayerEnum[status.key]?.icon || null;
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
};
</script>
