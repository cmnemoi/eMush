<template>
    <aside class="details-container">
        <h2 class="details-title">{{ details.fullName }}</h2>

        <div class="value" v-html="formatText(details.age)"/>
        <div class="value" v-html="formatText(details.employment)"/>
        <div class="value" v-html="formatText(details.abstract)"/>

        <div class="portrait-wrapper">
            <img :src="portrait" />
        </div>
    </aside>
</template>

<script setup lang="ts">
import { characterEnum } from "@/enums/character";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { computed } from "vue";

const props = defineProps<{
    characterName: string;
    details: {
        fullName: string;
        age: string;
        employment: string;
        abstract: string;
    }
}>();

const portrait = computed(() =>
    characterEnum[props.characterName]?.portrait ?? getImgUrl("items/todo.png")
);
</script>

<style scoped>
.details-container {
    background-color: #222b6b;
    border: 1px solid #576077;
    padding: 10px;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.4);
    height: fit-content;
}

.details-title {
    font-size: 1.8rem;
    margin: 0 0 8px 0;
    text-shadow: 0 0 3px rgba(255, 255, 255, 0.4);
}

.value {
    color: #b1c5f9;
    font-style: italic;
    line-height: 1.4;
    display: block;
}

.value :deep(strong) {
    color: #ff4f79;
}

.portrait-wrapper {
    background-color: rgba(54, 76, 148, 0.3);
    margin-top: 0.4rem;
    padding: 10px;
}

.portrait-wrapper img {
    width: 100%;
    display: block;
}
</style>
