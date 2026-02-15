<template>
    <Tippy>
        <div class="personal-notes-button" :class="{'active': highlighted}">
            <img :src="getImgUrl('ui_icons/notepad.png')" alt="🖉">
        </div>

        <template #content>
            <h1 v-html="formatContent($t('personalNotes.tooltipTitle'))"/>
            <p v-html="formatContent($t('personalNotes.tooltipDescription'))"/>
        </template>
    </Tippy>
</template>

<script setup lang="ts">
import { Tippy } from "vue-tippy";
import { getImgUrl } from "@/utils/getImgUrl";

defineProps(
    {
        highlighted: {
            type: Boolean,
            required: false,
            default: false
        }
    }
);
</script>

<style scoped lang="scss">
.personal-notes-button {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 25px;
    height: 25px;
    z-index: 10;
    background-color: #133951;
    border: 1px solid #1d5f80;
    filter: brightness(0.7);
    cursor: pointer;

    img {
        width: 15px;
        margin: auto;
    }
    &.active {
        filter: brightness(1);
    }
    &:hover {
        filter: brightness(1);
    }
}
</style>
