<template>
    <Tippy
        theme="mush"
        :hide-on-click="false"
        :allow-h-t-m-l="true"
        :max-width="280"
        :follow-cursor="true"
        :inline-positioning="true"
        placement="bottom-start"
        :duration="[null, 50]"
        class="tippy-tooltip"
    >
        <slot name="tooltip-trigger" />
        <template #content>
            <slot name="tooltip-content" :formatContent="formatContent" />
        </template>
    </Tippy>
</template>

<script lang="ts">
import { formatText } from "@/utils/formatText";
import { Tippy } from 'vue-tippy';
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Tooltip",
    components: { Tippy },
    methods:{
        formatContent(value: string): string {
            return !value ? '' : formatText(value.toString());
        }
    }
});
</script>
