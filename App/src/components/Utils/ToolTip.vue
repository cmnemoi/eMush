<template>
    <Tippy
        theme="mush"
        :hide-on-click="false"
        :allowHTML="true"
        :max-width="280"
        :follow-cursor="true"
        :inline-positioning="true"
        placement="bottom-start"
        :duration="[null, 50]"
        class="tippy-tooltip"
    >
        <slot name="tooltip-trigger" />
        <template #content>
            <slot name="tooltip-content" :formatContent="formatContent"></slot>
        </template>
    </Tippy>
</template>

<script>
import { formatText } from "@/utils/formatText";
import { Tippy } from 'vue-tippy';

export default {
    name: "Tooltip",
    components: { Tippy },
    methods:{
        formatContent(value) {
            return !value ? '' : formatText(value.toString());
        }
    }
};
</script>
