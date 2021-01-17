<template>
    <span
        v-for="(status, key) in statuses"
        :key="key"
        class="status"
    >
        <img :src="statusIcon(status)">
        <span v-if="status.charge">x{{ status.charge }}</span>
    </span>
</template>

<script>
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum } from "@/enums/status.item.enum";

export default {
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
