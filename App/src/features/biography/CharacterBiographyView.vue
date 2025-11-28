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
        <h2 class="full-row">{{ $t('biography.pageTitle') }}</h2>
        <CharacterBiographyDetails class="details" :character-name="characterName" :details="details" />
        <CharacterBiographyTimeLine class="timeline" :bio="biography" />
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

<style scoped>
.full-row {
    grid-column: 1 / -1;
    margin-bottom: auto;
    margin-top: 0.4rem;
    order: 1;
}

.box-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.navigation-bar {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 6px 0;
    margin: 0;
    min-height: 45px;
    box-sizing: border-box;
    order: 0;
}

.details {
    order: 2;
}

.timeline {
    order: 3;
}

@media (min-width: 768px) {
    .box-container {
        grid-template-columns: 1fr 250px;
    }

    .details {
        order: 3;
    }

    .timeline {
        order: 2;
    }
}
</style>
