<template>
    <li class="character-signature">
        <img class="body" :src="getCharacterBodyFromKey(characterKey)" :alt="getCharacterNameFromKey(characterKey)"> {{ getCharacterNameFromKey(characterKey) }}
    </li>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { characterEnum } from "@/enums/character";

export default defineComponent ({
    name: "CharacterSignature",
    props: {
        characterKey: {
            type: String,
            required: true
        }
    },
    methods: {
        getCharacterNameFromKey(characterKey: string) {
            return characterEnum[characterKey].name;
        },
        getCharacterBodyFromKey(characterKey: string) {
            return characterEnum[characterKey].body;
        }
    }
});
</script>

<style lang="scss" scoped>
li {
    margin: .1em .6em;
    display: flex;
    align-items: center;

    img { margin-right: 0.4em; }
}
</style>
