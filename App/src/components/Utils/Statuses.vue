<template>
    <span
        v-for="(status, key) in statuses"
        :key="key"
        class="status"
    >
        <Tippy>
            <img :src="statusIcon(status)">
            <span v-if="status.charge">{{ status.charge }}</span>
            <template #content>
                <h1 v-html="formatContent(status.name)" />
                <p v-html="formatContent(status.description)" />
            </template>
        </Tippy>
    </span>
</template>

<script lang="ts">
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum } from "@/enums/status.item.enum";
import { defineComponent } from "vue";
import { Status } from "@/entities/Status";

export default defineComponent ({
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
