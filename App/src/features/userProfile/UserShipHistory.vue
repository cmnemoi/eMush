<template>
    <div class="user-profile-ships-container">
        <h2>{{ $t('userProfile.shipHistory.trips') }}</h2>
        <Datatable
            :headers="fields"
            :loading="loading"
            :row-data="shipsHistory.data"
            :pagination="pagination"
            :filter="filter"
            @pagination-click="paginationClick"
        >

            <template #header-characterName>
                <div class="header-text">
                    <span :class="{'header-text-name': field.emote}">{{ $t(field.name) }}</span>
                    <Tippy v-if="field.emote" tag="span" class="header-text-emote">
                        <span v-html="formatText(field.emote)"/>
                        <template #content>
                            {{ $t(field.name) }}
                        </template>
                    </Tippy>
                </div>
            </template>
            <template #row-characterName="slotProps">
                <div class="character-name">
                    <Tippy tag="span">
                        <span v-html="formatText(slotProps.characterBody)"/>
                        <template #content>
                            {{ slotProps.characterName }}
                        </template>
                    </Tippy>
                    <span class="hide-on-desktop-m">{{ slotProps.characterName }}</span>
                </div>
            </template>

            <template #row-actions="slotProps">
                <router-link class="router" :to="{ name: 'TheEnd', params: { closedDaedalusId: slotProps.daedalusId } }">
                    <img :src="getImgUrl('ui_icons/right.png')" id="arrow" /> <span class="hide-on-desktop-m">{{ $t('userProfile.shipHistory.awards') }}</span>
                </router-link>
            </template>
        </Datatable>
    </div>
</template>

<script setup lang="ts">
import Datatable from '@/components/Utils/Datatable/Datatable.vue';
import { ShipHistory } from '@/features/userProfile/models';
import { getImgUrl } from '@/utils/getImgUrl';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useStore } from 'vuex';
import { formatText } from "@/utils/formatText";

const route = useRoute();
const store = useStore();

const language = computed((): string => store.getters['locale/currentLocale']);
const shipsHistory = computed((): ShipHistory => store.getters['userProfile/shipsHistory']);
const loading = computed((): boolean => store.getters['userProfile/loading']);

const fields = ref([
    {
        key: 'characterName',
        name: 'userProfile.shipHistory.character',
        // TODO: Switch with :female_admin: when we can access user gender
        emote: ':male_admin:',
        sortable: false,
        slotRow: true
    },
    {
        key: 'daysSurvived',
        name: 'userProfile.shipHistory.days',
        emote: ':ic_watch:',
        sortable: false
    },
    {
        key: 'nbExplorations',
        name: 'userProfile.shipHistory.explorations',
        emote: ':ic_explorer:',
        sortable: false
    },
    {
        key: 'nbResearchProjects',
        name: 'userProfile.shipHistory.researches',
        emote: ':ic_research:',
        sortable: false
    },
    {
        key: 'nbNeronProjects',
        name: 'userProfile.shipHistory.neronProjects',
        emote: ':ap_core:',
        sortable: false
    },
    {
        key: 'nbScannedPlanets',
        name: 'userProfile.shipHistory.scannedPlanets',
        emote: ':ic_planet_scanned:',
        sortable: false
    },
    {
        key: 'titles',
        name: 'userProfile.shipHistory.titles',
        emote: ':st_commander:',
        sortable: false
    },
    {
        key: 'triumph',
        name: 'userProfile.shipHistory.triumph',
        emote: ':triumph:',
        sortable: false
    },
    {
        key: 'endCause',
        name: 'userProfile.shipHistory.endCause',
        sortable: false
    },
    {
        key: 'actions',
        name: 'Daedalus',
        emote: ':ic_note:',
        sortable: false,
        slotRow: true
    }
]);

const pagination = ref({
    currentPage: 1,
    pageSize: 6,
    totalItems: 1,
    totalPage: 1
});

const filter = ref('');

const loadData = async () => {
    await store.dispatch('userProfile/loadShipsHistory', {
        userId: route.params.userId as string,
        page: pagination.value.currentPage,
        itemsPerPage: pagination.value.pageSize,
        language: language.value
    });
    pagination.value.totalItems = shipsHistory.value.totalItems;
    pagination.value.totalPage = Math.ceil(pagination.value.totalItems / pagination.value.pageSize);
};


const paginationClick = (page: number) => {
    pagination.value.currentPage = page;
    loadData();
};

onMounted(() => loadData());
watch(language, () => loadData());
watch(route, () => loadData());

</script>

<style lang="scss" scoped>
.user-profile-ships-container {
    padding: 20px;
}

.router {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2px 5px;
}

#arrow {
    top: 0;
}

.hide-on-desktop-m {
    display: initial;

    @media only screen and (max-width: $breakpoint-desktop-m) { display: none;}
}

.character-name {
    flex-direction: row;
    justify-content: center;
    align-items: center;
    gap: 4px;
}
</style>
