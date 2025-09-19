<template>
    <div class="localechange">
        <ol>
            <Tippy
                tag="img"
                v-for="(lang, langKey) in langs"
                :class="{ flag: true, selected: selectedLocale === langKey }"
                :key="`Lang${langKey}`"
                :value="lang.caption"
                @click="updateLocale(langKey as string)"
                :src="lang.icon"
                :alt="lang.caption"
                :title="lang.caption"
            >
                <template #content>
                    <p>{{ lang.caption }}</p>
                </template>
            </Tippy>
        </ol>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { gameLocales } from "@/i18n";
import { mapGetters, mapActions } from "vuex";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    name: 'LocaleChange',
    components: { Tippy },
    data () {
        return { langs: gameLocales };
    },
    computed: {
        ...mapGetters({
            selectedLocale: 'locale/currentLocale'
        })
    },
    methods: {
        ...mapActions({
            updateLocale: 'locale/updateLocale'
        })
    }
});
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
