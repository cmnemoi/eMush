<template>
    <div class="character-grid">
        <div
            v-for="(characterInfo, characterKey) in characters"
            :key="characterKey"
            class="character-item"
            @click="$emit('character-selected', characterInfo.keyName.toLowerCase())">
            <img :src="characterInfo.head" :alt="characterInfo.name">
            <div class="character-name">{{ characterInfo.name }}</div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { CharacterInfos } from "@/enums/character";

export default defineComponent({
    name: "RichTextEditorCharacterEmotes",
    props: {
        characters: {
            type: Object as PropType<{[key: string]: CharacterInfos}>,
            required: true
        }
    },
    emits: ["character-selected"],
    setup() {
        return {
            getImgUrl
        };
    }
});
</script>

<style lang="scss" scoped>
.character-grid {
    display: grid;
    position: absolute;
    top: 40px;
    left: 0px;
    z-index: auto;
    grid-template-columns: repeat(5, 1fr);
    gap: 3px;
    margin-bottom: 10px;
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #aad4e5;
    border-radius: 3px;
    padding: 3px;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.character-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 2px;
    border-radius: 3px;
    transition: background-color 0.2s;

    &:hover {
        background-color: #e9f5fb;
    }

    img {
        width: 16px;
        height: 16px;
        object-fit: cover;
        border-radius: 5%;
    }

    .character-name {
        margin-top: 4px;
        font-size: 11px;
        text-align: center;
    }
}
</style>
