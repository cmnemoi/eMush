<template>
    <tippy theme="mush"
        hideOnClick=false
        allowHTML=false
        maxWidth=280
        followCursor=true
        inlinePositioning=true
        placement=bottom-start
        duration="[null, 50]"
        class="tippy-tooltip">
            <slot name='tooltip-trigger' />
            <template #content>
                <slot name='tooltip-content' :formatContent="formatContent"></slot>
            </template>
    </tippy>
</template>

<script>
import { formatText } from "@/utils/formatText";

    export default {
    name: "Tooltip",
    methods:{
        formatContent(value) {
            return !value ? '' : formatText(value.toString());
            }
        },
    }
</script>