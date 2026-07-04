<template>
    <div class="sanction-banner-container" v-if="userSanctions.length > 0">
        <div class="sanction-banner">
            <div class="sanction-list">
                <template v-for="(sanction, index) in sanctions.slice(0, reduced ? 1 : sanctions.length)" :key="index">
                    <h1 class="sanction-title">
                        {{ getTranslatedSanctionType(sanction) }} {{ $t('moderation.sanction.until') }} {{ sanction.endDateGivenLocale(locale) }}
                    </h1>
                    <div class="sanction-reason">
                        {{$t('moderation.sanction.reason')}}: {{ $t(`moderation.reason.${sanction.reason}`) }}
                    </div>
                    <p v-if="!reduced" class="sanction-content">
                        <span>{{ sanction.message }}</span>
                    </p>
                </template>
            </div>
            <button class="button-toggle-show-all" @click="reduced = !reduced">
                {{ reduced ? $t('moderation.showAll') + ' (' + sanctions.length + ')' : $t('moderation.reduce') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ModerationSanction } from "@/entities/ModerationSanction";
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps<{ userSanctions: ModerationSanction[] }>();
const { t, locale } = useI18n();
const reduced = ref(true);

// Display bans first and sort by endDate
const sanctions = computed(() => [...props.userSanctions].sort((s1, s2) => {
    if (s1.isWarning && !s2.isWarning) {
        return 1;
    }
    if (!s1.isWarning && s2.isWarning) {
        return -1;
    }
    return s1.endDate > s2.endDate ? -1 : 1;
}));

function getTranslatedSanctionType(sanction: ModerationSanction) {
    return sanction.isWarning ? t('moderation.sanction.warning') : t('moderation.player.banned');
}
</script>


<style scoped>
.sanction-banner-container {
    position: fixed;
    bottom: 0;
    width: 100%;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    padding: 10px;
    box-sizing: border-box;
    z-index: 10;
}

.sanction-banner {
    background-color: #f05b76;
    color: black;
    padding: 10px;
    margin-bottom: 2px;
    border-radius: 5px;
    width: 100%;
}

.sanction-list {
    max-height: 50vh;
    overflow-y: auto;
}

.sanction-title {
    margin: 0;
    font-size: 18px;
}

.sanction-reason {
    margin-top: 3px;
    font-weight: bold;
    font-style: italic;
}

.sanction-content {
    margin-top: 3px;
}

.button-toggle-show-all {
    color: #4d4d4d;
    margin-left: auto;
}
</style>
