<template>
    <table id="tableComponent" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th  v-for="field in headers" :key='field' @click="sortTable(field)" >
                    {{ field }} <i class="bi bi-sort-alpha-down" aria-label='Sort Icon'></i>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in rows" :key='row'>
                <td v-for="field in headers" :key='field'>{{row[field]}}</td>
            </tr>
        </tbody>
    </table>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ApiService from "@/services/api.service";

export default defineComponent ({
    name: "Datatable",
    props:{
        headers:{
            type: Array,
        },
        uri: {
            type: String
        },
    },
    data() {
        return {
            rows: [],
            totalItem: 0,
            sort: {},
            page: 1,
            size: 10,
        };
    },
    methods: {
        sortTable(field: string): void {
            this.sort = {
                field: field,
            };
        },
        loadData() {
            if (this.uri) {
                const params = {
                    header: {
                        'accept' : 'application/ld+json'
                    },
                    params: {}
                };
                if (this.page) {
                    params.params['page'] = this.page;
                }
                if (this.size) {
                    params.params['itemsPerPage'] = this.size;
                }
                ApiService.get(this.uri, params)
                    .then((result) => {
                        return result.data;
                    })
                    .then((remoteRowData:any) => {
                        this.rows = remoteRowData['hydra:member'];
                        this.totalItem = remoteRowData['hydra:totalItems'];
                    });
                ;
            }
        }
    },
    beforeMount() {
        this.loadData();
    }
});
</script>

<style scoped>

</style>