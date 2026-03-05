<template>
    <div class="box-container">
        <div class="navigation-bar">
            <div v-for="(item, index) in charactersOrder" :key="`${index}`">
                <button class="action-button" :title="characterEnum[item].completeName" @click="goToCharacter(item)">
                    <img :src="characterEnum[item].head" :alt="characterEnum[item].completeName" />
                    {{ characterEnum[item].name }}
                </button>
            </div>
        </div>
        <h2>{{ $t('biography.pageTitle') }}</h2>
        <div class="biography-container">
            <CharacterBiographyTimeLine :bio="biography" />
            <CharacterBiographyDetails class="details" :character-name="characterName" :details="details" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { CharacterEnum, characterEnum } from "@/enums/character";
import router from "@/router";
import { computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import CharacterBiographyDetails from "./CharacterBiographyDetails.vue";
import CharacterBiographyTimeLine from "./CharacterBiographyTimeLine.vue";

const route = useRoute();
const store = useStore();

const characterName = computed(() => route.params.characterName as string);
const language = computed(() => store.getters['locale/currentLocale']);
const characterBio = computed(() => store.getters['biography/biography']);
const biography = computed(() => characterBio.value?.biography ?? []);
const details = computed(() => characterBio.value?.details ?? {});

// Exclude characters non-playable (without portrait)
const charactersOrder = Object.values(CharacterEnum)
    .filter(key => characterEnum[key]?.portrait)
    .map(key => key);

watch([characterName, language], () => {
    return store.dispatch('biography/loadCharacterBio', {
        characterName: characterName.value,
        language: language.value
    });
}, { immediate: true });

const goToCharacter = (character: CharacterEnum) => {
    router.push({ name: "CharacterBiographyView", params: { characterName: character } });
};
</script>

<style scoped lang="scss">
.navigation-bar {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    padding: 6px 0;
}

.biography-container {
    flex-direction:row;
    gap: 1rem;

    @media screen and (max-width: $breakpoint-desktop-s) {
        flex-direction: column-reverse;
    }

    .details {
        flex: 0;
        min-width: 270px;
    }
}
</style>
