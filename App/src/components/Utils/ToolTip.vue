<template>
    <tippy class="tippy-tooltip">
        <slot name='tooltip-trigger' />
        <template #content>
            <slot name='tooltip-content' />
        </template>
    </tippy>
</template>

<script>
import { formatText } from "@/utils/formatText";

    export default {
    name: "Tooltip",
    methods:{
        formatContent(value) {
            if (! value) return '';
            return formatText(value.toString());
            }
        },
    }
</script>