<template>
    <div class="datatable-container">
        <Spinner :loading="loading"></Spinner>
        <table id="tableComponent" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th
                        v-for="field in headers"
                        :key='field'
                        @click="sortTable(field)"
                        :class=sortClassname(field)
                    >
                        <slot v-if="field.slot" :name="[`header-${field.key}`]" v-bind="field" />
                        <span v-else class="header-text">{{ field.name }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rowData" :key='row'>
                    <td v-for="field in headers" :key='field'>
                        <slot v-if="field.slot" :name="[`row-${field.key}`]" v-bind="row" />
                        <span v-else>{{ row[field.key] }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="datable-pagination-container">
            <Pagination
                :page-count="pagination.totalPage"
                :click-handler="paginationClick"
                :prev-text="'Prev'"
                :next-text="'Next'"
                :container-class="'className'"
            ></Pagination>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import Pagination from "@/components/Utils/Datatable/Pagination.vue";
import Spinner from "@/components/Utils/Spinner.vue";

export interface Header {
    key: string,
    name: string | null,
    sortable: boolean | null
}

export default defineComponent ({
    name: "Datatable",
    components: {
        Spinner,
        Pagination
    },
    emits: {
        'pagination-click': null,
        'sort-table': null
    },
    props:{
        headers:{
            type: Array,
            required: true
        },
        uri: {
            type: String
        },
        loading: {
            type: Boolean,
            default: false
        },
        rowData: {
            required: true
        },
        pagination: {
            type: Object
        }
    },
    data() {
        return {
            rows: [],
            totalItem: 1,
            totalPage: 1,
            sortField: '',
            sortDirection: 'DESC',
            page: 1,
            pageSize: 5
        };
    },
    methods: {
        sortTable(selectedField: Header): void {
            if (!selectedField.sortable) {
                return;
            }
            if (this.sortField === selectedField.key) {
                switch (this.sortDirection) {
                case 'DESC':
                    this.sortDirection = 'ASC';
                    break;
                case 'ASC':
                    this.sortDirection = 'DESC';
                    break;
                }
            } else {
                this.sortDirection = 'DESC';
            }
            this.sortField = selectedField.key;
            this.$emit('sort-table', selectedField);
        },
        paginationClick(page: number) {
            this.$emit('pagination-click', page);
        },
        sortClassname(field: Header) {
            if (!field.sortable) {
                return null;
            }
            let className = "sorting";
            if (this.sortField === field.key) {
                className += this.sortDirection === "ASC" ? " sorting_asc" : " sorting_desc";
            }
            return className;
        }
    }
});
</script>

<style lang="scss" scoped>

.datatable-container {
    position: relative;
}

table {
    border-collapse: collapse;
    border-spacing: 0;

    td {
        padding: 8px 10px;
    }

    th {
        position: relative;
        padding: 10px;
        padding-right: 26px;
        border-bottom: 1px solid white;
        text-align: left;
        font-weight: bold;
    }

    .sorting_asc:after {
        opacity: .6;
    }

    .sorting_desc:before {
        opacity: .6;
    }
    .sorting:before {
        bottom: 50%;
        content: "▴";
        position: absolute;
        display: block;
        right: 10px;
        line-height: 9px;
        font-size: .9em;
    }
    .sorting:after {
        top: 50%;
        content: "▾";
        position: absolute;
        display: block;
        right: 10px;
        line-height: 9px;
        font-size: .9em;
    }
}

.datable-pagination-container {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: center;
    padding: 10px;
}
</style>
