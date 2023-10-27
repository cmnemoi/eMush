<template>
    <div class="news_filter_options">
        <router-link :to="{ name: 'AdminSecretsCreate'}">
            {{ $t("admin.secrets.create") }}
        </router-link>
    </div>
    <Datatable :headers='fields' :row-data="rowData" :pagination="pagination">
        <template #header-updateSecret>
            Update secret
        </template>
        <template #row-updateSecret="slotProps">
            <div class="flex-row">
                <router-link :to="{ name: 'AdminSecretsEdit', params: { secret: slotProps.name } }">
                    {{ $t("admin.edit") }}
                </router-link>
            </div>
        </template>
    </Datatable>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminService from "@/services/admin.service";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";

export default defineComponent ({
    name: 'AdminSecretsList',
    components: {
        Datatable,
    },
    methods: {
        async getSecrets() {
            return await AdminService.getSecrets().then((response) => {
                this.rowData = response.data;
            });
        },
        async updateSecret(name: string, value: string) {
            return await AdminService.editSecret({ 'name': name, 'value': value }).then((response) => {
                this.getSecrets();
            });
        }
    },
    data() {
        return {
            fields: [
                {
                    key: 'name',
                    name: 'Name',
                    sortable: true
                },
                {
                    key: 'updateSecret',
                    name: 'New value',
                    sortable: false,
                    slot: true
                }
            ],
            pagination: {
                currentPage: 1,
                pageSize: 10,
                totalItem: 1,
                totalPage: 1
            },
            rowData: [],
            envVar: {
                name: '',
                value: '',
            },
        };
    },
    beforeMount() {
        this.getSecrets();
    }
});
</script>

<style lang="scss" scoped>

.news_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}

a {
    @include button-style();
    padding: 2px 15px 4px;
}

</style>