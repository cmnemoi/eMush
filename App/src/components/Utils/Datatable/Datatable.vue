<template>
    <div class="datatable-container">
        <Spinner :loading="loading"></Spinner>
        <table id="tableComponent" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th
                        v-for="field in headers"
                        :key='field.key'
                        @click="sortTable(field)"
                        :class=sortClassname(field)
                    >
                        <slot v-if="field.slot" :name="`header-${field.key}`" v-bind="field" />
                        <span v-else class="header-text">{{ $t(field.name) }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rowData" :key='row.id' @click="onRowClick(row)">
                    <td v-for="field in headers" :key='field.key'>
                        <slot v-if="field.slot" :name="`row-${field.key}`" v-bind="row" />
                        <span v-else>
                            <img
                                :src="row[field.image]"
                                v-if="row[field.image]"
                                :alt="row[field.name]"
                                id="row-image"
                            />
                            {{ $t(String(field.subkey ? row[field.key][field.subkey] : row[field.key])) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="datable-pagination-container">
            <Pagination
                :page-count="Math.ceil(pagination.totalPage)"
                :click-handler="paginationClick"
                :prev-text="$t('util.prev')"
                :next-text="$t('util.next')"
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
    subkey: string | null,
    name: string | null,
    sortable: boolean | null,
    image: any | null,
}

export default defineComponent ({
    name: "Datatable",
    components: {
        Spinner,
        Pagination
    },
    emits: {
        'pagination-click': null,
        'sort-table': null,
        'row-click': null
    },
    props: {
        headers: {
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
        onRowClick(row: any) {
            this.$emit('row-click', row);
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

.datatable-container { position: relative; }

table {
    background: #222b6b;
    border-radius: 5px;
    border-collapse: collapse;

    tbody tr {
        border-top: 1px solid rgba(0,0,0,0.2);

        &:hover,
        &:active { background: rgba(255, 255, 255, .03); }

        td:last-child { width: 0; }
    }

    th, td {
        padding: 1em 0.5em 1em 1.2em;
        vertical-align: middle;

        &::v-deep(a), &::v-deep(button) {
            @include button-style();
            width: fit-content;
            flex: 1;
            padding: 2px 15px 4px;
            white-space: nowrap;
        }
    }

    th {
        position: relative;
        letter-spacing: .05em;
        text-align: left;
        font-weight: bold;
        border-bottom: 1px solid rgba(255, 255, 255, .75);

        &.sorting { cursor: pointer; }
    }

    .sorting_asc:before, .sorting_desc:after { opacity: 1 !important; }

    .sorting:before, .sorting:after {
        position: absolute;
        display: block;
        opacity: 0.6;
        left: 0.4em;
        line-height: 9px;
        font-size: .9em;
        cursor: pointer;
    }

    .sorting:before {
        bottom: 50%;
        content: "▴";
    }

    .sorting:after {
        top: 50%;
        content: "▾";
    }
}

.datable-pagination-container {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: center;
    padding: 10px;
}

#row-image {
    padding-left: 6px;
    padding-right: 6px;
}
</style>
