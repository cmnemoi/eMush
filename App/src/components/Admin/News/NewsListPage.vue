<template>
    <div class="news_list_container">
        <div class="table-filter-container">
            <label>{{ $t('admin.show') }}
                <select v-model="pagination.pageSize" @change="updateFilter">
                    <option
                        v-for="option in pageSizeOptions"
                        :value="option.value"
                        :key=option.value
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <router-link :to="{ name: 'AdminNewsWrite'}">
                {{$t("admin.newsList.create")}}
            </router-link>
        </div>
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            :filter="filter"
            @pagination-click="paginationClick"
            @sort-table="sortTable"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="slotProps">
                <router-link :to="{ name: 'AdminNewsEdit', params: { newsId : slotProps.id } }">{{ $t('admin.edit') }}</router-link>
            </template>
        </Datatable>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import DataTableMixin from "@/mixin/dataTableMixin";

export default defineComponent({
    name: "NewsListPage",
    mixins: [DataTableMixin],
    components: { Datatable },
    data() {
        return {
            endpoint: urlJoin(import.meta.env.VITE_APP_API_URL + 'news'),
            fields: [
                {
                    key: 'frenchTitle',
                    name: 'admin.newsList.newsTitle',
                    sortable: true
                },
                {
                    key: 'publicationDate',
                    name: 'admin.newsList.publicationDate',
                    sortable: true
                },
                {
                    key: 'updatedAt',
                    name: 'admin.newsList.updatedAt',
                    sortable: true
                },
                {
                    key: 'actions',
                    name: 'Action',
                    sortable: false,
                    slot: true
                }
            ],
            filterField: "newsName"
        };
    }
});
</script>

<style lang="scss" scoped>
a {
    @include button-style();

    & {
        padding: 2px 15px 4px;
    }
}
</style>
