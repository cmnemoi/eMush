<template>
    <Tippy
        tag="span"
        v-for="status in statuses"
        :key="status.id"
        class="status"
    >
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
import { getImgUrl } from "@/utils/getImgUrl";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    components: {
        Tippy
    },
    props: {
        statuses: Array<Status>,
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
                        return getImgUrl('status/disease.png');
                    case "disorder":
                        return getImgUrl('status/disorder.png');
                    case "injury":
                        return getImgUrl('status/injury.png');
                    }
                    return null;
                case "item":
                case "equipment":
                    return statusItemEnum[status.key]?.icon || null;
                case "room":
                    return getImgUrl('alerts/fire.png');
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
