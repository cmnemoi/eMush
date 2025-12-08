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
            <template #header-actions>
                {{ $t('userProfile.shipHistory.daedalus') }}
            </template>
            <template #row-actions="slotProps">
                <router-link class="router" :to="{ name: 'TheEnd', params: { closedDaedalusId: slotProps.daedalusId } }">
                    <img :src="getImgUrl('ui_icons/right.png')" id="arrow" /> {{ $t('userProfile.shipHistory.awards') }}
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

const route = useRoute();
const store = useStore();

const language = computed((): string => store.getters['locale/currentLocale']);
const shipsHistory = computed((): ShipHistory => store.getters['userProfile/shipsHistory']);
const loading = computed((): boolean => store.getters['userProfile/loading']);

const fields = ref([
    {
        key: 'characterName',
        name: 'userProfile.shipHistory.character',
        sortable: false
    },
    {
        key: 'daysSurvived',
        name: 'userProfile.shipHistory.days',
        sortable: false
    },
    {
        key: 'nbExplorations',
        name: 'userProfile.shipHistory.explorations',
        sortable: false
    },
    {
        key: 'nbResearchProjects',
        name: 'userProfile.shipHistory.researches',
        sortable: false
    },
    {
        key: 'nbNeronProjects',
        name: 'userProfile.shipHistory.neronProjects',
        sortable: false
    },
    {
        key: 'nbScannedPlanets',
        name: 'userProfile.shipHistory.scannedPlanets',
        sortable: false
    },
    {
        key: 'titles',
        name: 'userProfile.shipHistory.titles',
        sortable: false
    },
    {
        key: 'triumph',
        name: 'userProfile.shipHistory.triumph',
        sortable: false
    },
    {
        key: 'endCause',
        name: 'userProfile.shipHistory.endCause',
        sortable: false
    },
    {
        key: 'actions',
        name: 'userProfile.shipHistory.goToTheEnd',
        sortable: false,
        slot: true
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
    padding-left: 5px;
}

#arrow {
    top: 0;
}
</style>
