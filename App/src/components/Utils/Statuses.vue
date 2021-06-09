<template>
    <span
        v-for="(status, key) in statuses"
        :key="key"
        class="status"
    >
        <Tooltip>
            <template v-slot:tooltip-trigger>
                <img :src="statusIcon(status)">
                <span v-if="status.charge">{{ status.charge }}</span>
            </template>
            <template #tooltip-content>
                <h1 v-html="formatContent(status.name)" />
                <p v-html="formatContent(status.description)" />
            </template>
        </Tooltip>
    </span>
</template>

<script>
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum } from "@/enums/status.item.enum";
import Tooltip from "@/components/Utils/ToolTip";
import { formatText } from "@/utils/formatText";

export default {
    components: {Tooltip},
    props: {
        statuses: Array,
        type: Array
    },
    methods:{
        formatContent(value) {
            if (! value) return '';
            return formatText(value.toString());
            }
        },
    computed: {
        statusIcon() {
            return (status) => {
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
};
</script>
