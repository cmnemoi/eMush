<template>
    <div class="localechange">
        <ol>
            <Tippy
                tag="img"
                v-for="(lang, langKey) in locales"
                :class="{ flag: true, selected: selectedLocale === langKey }"
                :key="`Lang${langKey}`"
                :value="lang.caption"
                @click="updateLocale(langKey as string)"
                :src="lang.icon"
                :alt="lang.caption"
            >
                <template #content>
                    <p>{{ lang.caption }}</p>
                </template>
            </Tippy>
        </ol>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { gameLocales } from '@/i18n';
import { Tippy } from 'vue-tippy';

const store = useStore();
const locales = ref(gameLocales);
const selectedLocale = computed(() => store.getters['locale/currentLocale']);
const updateLocale = (locale: string) => store.dispatch('locale/updateLocale', locale);
</script>

<style lang="scss" scoped>
.localechange {
	width: 100%;
}

ol {
	list-style: none;
	padding: 0;
	margin: 0;
	display: flex;
	justify-content: center;
	align-items: center;
	width: 100%;
	gap: 8px;
}

.flag {
	cursor: pointer;
	box-shadow: none;
	border: 1px solid rgb(108, 113, 136);
	object-fit: cover;

	&:not(.selected):hover {
		box-shadow: $orange 0px 0px 8px;
	}
}

.selected {
	box-shadow: $orange 0px 0px 8px;
}
</style>
