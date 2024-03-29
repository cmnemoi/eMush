<template>
    <Tippy
        tag="span"
        v-for="(status, key) in statuses"
        :key="key"
        class="status">
        <img :src="statusIcon(status)">
        <span v-if="status.charge !== null" class="charge">{{ status.charge }}</span>
        <template #content>
            <h1 v-html="formatContent(status.name)" />
            <p v-html="formatContent(status.description)" />
        </template>
    </Tippy>
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
                    switch (status.diseaseType) {
                    case "disease":
                        return 'src/assets/images/status/disease.png';
                    case "disorder":
                        return 'src/assets/images/status/disorder.png';
                    case "injury":
                        return 'src/assets/images/status/injury.png';
                    }
                    return null;
                case "item":
                case "equipment":
                    return statusItemEnum[status.key]?.icon || null;
                case "room":
                    return 'src/assets/images/alerts/fire.png';
                default:
                    return null;
                }
            };
        }
    }
});
</script>

<style lang="scss" scoped>

.charge {
    margin-left: 1px;
    text-shadow: 0 0 4px black;
}

</style>
