<template>
    <div class="user_list_container">
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
            <label>{{ $t('admin.search') }}:
                <input
                    v-model="filter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
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
                <router-link :to="{ name: 'AdminDiseaseCauseConfigDetail', params: { diseaseCauseConfigId : slotProps.id } }">{{ $t('admin.edit') }}</router-link>
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
    name: "DiseaseCauseConfigListPage",
    mixins: [DataTableMixin],
    components: { Datatable },
    data() {
        return {
            endpoint: urlJoin(import.meta.env.VITE_APP_API_URL + 'disease_cause_configs'),
            fields: [
                {
                    key: 'id',
                    name: 'Id',
                    sortable: true
                },
                {
                    key: 'name',
                    name: 'Name',
                    sortable: true
                },
                {
                    key: 'actions',
                    name: 'Action',
                    sortable: false,
                    slot: true
                }
            ]
        };
    }
});
</script>
