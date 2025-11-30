<template>
    <Tippy tag="span">
        <router-link :to="`biography/${character.key}`">
            <img class="book" :src="getImgUrl('ui_icons/book.png')" alt="biography" />
        </router-link>
        <template #content>
            <p>{{ $t('biography.readCharacterBiography', { characterName: character.name }) }}</p>
        </template>
    </Tippy>
</template>

<script setup lang="ts">
import { defineProps } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { Character } from "@/entities/Character";

defineProps<{character: Character}>();
</script>

<style lang="scss" scoped>
.book {
    margin-bottom: 6px;
}
</style>
