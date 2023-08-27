<template>
    <textarea v-model="query" />
    <button class="action-button" type="submit" @click="execute">
        {{$t("admin.sql.execute")}}
    </button>    
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminService from "@/services/admin.service";
import store from "@/store";

export default defineComponent({
    name: "AdminSQLQueryPage",

    data() : any {
        return {
            query: null,
            errors: {}
        };
    },
    methods: {
        execute() {
            AdminService.executeSQLQuery(this.query)
                .then(() => {
                    this.$router.push({ name: "AdminSQL" });
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    store.dispatch('gameConfig/setLoading', { loading: false });
                });
        },
    }
});
</script>

<style lang="scss" scoped>
    textarea {
        background: transparent;
        border: thin solid rgba(255, 255, 255, .25);
        color: #fff;
        font-size: 1.2em;
        font-weight: 300;
        letter-spacing: .05em;
        padding: 0.5em 0.5em 0.5em 0;
        width: 100%;
    }
</style>
