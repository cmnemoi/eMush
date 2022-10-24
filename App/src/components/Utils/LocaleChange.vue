<template>
    <div class="localechange">
        <ol>
            <img
                v-for="(lang, i) in langs"
                :key="`Lang${i}`"
                :value="lang.caption"
                @click="switchLocale(i)"
                :src="lang.icon" />
        </ol>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { gameLocales } from "@/i18n";

export default defineComponent ({
    name: 'LocaleChange',
    data () {
        return { langs: gameLocales };
    },
    methods: {
        switchLocale: function(locale :string) {
            if (this.$i18n.locale !== locale) {
                this.$i18n.locale = locale;
            }
        }
    }
});
</script>

<style lang="scss" scoped>

.localechange {
    img {
        width: auto;
        height: auto;
        margin: 0.6em auto .1em;
        padding: .6em;
        border-radius: .4em;
        background-color: rgba(15, 15, 67, .5);
    }
    position: fixed;
    bottom: 0;
}
</style>
