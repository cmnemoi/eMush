<template>
    <div class="emote-grid">
        <div
            v-for="(emote, i) in config.emoteEnum"
            :key="i"
            class="emote-item"
            @click="$emit('emote', emote)">
            <div class="emote-thumb">
                <img :src="config.iconEnum[emote]">
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { CharacterInfos } from "@/enums/character";
import {
    RichTextEditorEmoteButtonConfig
} from "@/components/Game/Communications/Messages/RichTextEditor/RichTextEditorConfig";

export default defineComponent({
    name: "RichTextEditorEmotePanel",
    props: {
        config: {
            type: Object as PropType<RichTextEditorEmoteButtonConfig>,
            required: true
        }
    },
    emits: ["emote"]
});
</script>

<style lang="scss" scoped>
.emote-grid {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin-top: -5px;
    padding: 3px;
    border: 2px solid #c2f3fc;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
    background-color: white;
}

.emote-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: background-color 0.2s;

    &:hover {
        background-color: #e9f5fb;
    }

    .emote-thumb {
        width: 16px;
        height: 16px;
        display: grid;
        place-items: center;
    }


    img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 5%;
    }
}
</style>
