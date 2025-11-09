<template>
    <Tippy
        tag="div"
        class="tab"
        :class="selected ? 'checked' : ''"
        @click="$emit('select')"
    >
        <img :src="config.icon">
    </Tippy>
</template>

<script lang="ts">
import { defineComponent, computed, PropType } from "vue";
import {
    RichTextEditorEmoteButtonConfig
} from "@/components/Game/Communications/Messages/RichTextEditor/RichTextEditorConfig";

export default defineComponent({
    name: "RichTextEditorButton",
    props: {
        selected: Boolean,
        config: {
            type: Object as PropType<RichTextEditorEmoteButtonConfig>,
            required: true
        }
    },
    emits: ['select']
});
</script>

<style lang="scss" scoped>
.tab {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    float: left;
    cursor: pointer;
    width: 31px;
    height: 25px;
    margin-right: 4px;

    * {
        z-index: 2;
    }

    &::after { // Background of the tab icons
        content: "";
        z-index: 1;
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(0deg, rgb(0, 116, 223) 6%, rgb(0, 116, 223) 46%, rgb(0, 142.0322394619, 229.018) 54%, rgb(0, 142.0322394619, 229.018) 94%, rgb(72.999125, 154.1474971973, 228.999875) 96%);

        @include corner-bezel(4.5px, 4.5px, 0);
    }

    &.checked,
    &.active,
    &:hover,
    &:focus {
        &::after {
            background: rgba(194, 243, 252, 1);
        }

        &.pirated::after { // Background of the tab icons
            background: rgba(255, 66, 89, 1);
        }
    }

    img {
        max-width: 16px;
        max-height: 16px;
    }
}
</style>
