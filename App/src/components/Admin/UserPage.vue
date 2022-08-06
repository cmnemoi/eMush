<template>
    <Datatable :headers='fields' :uri="uri"></Datatable>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive } from "vue";
import Datatable from "@/components/Utils/Datatable";
import UserService from "@/services/user.service";
import urlJoin from "url-join"; // Optional theme CSS

export default defineComponent({
    name: "UserPage",
    components: {
        Datatable,
    },
    data() {
        return {
            fields: [
                'username',
                'userId',
                'roles',
            ],
            rowData: [],
            uri: urlJoin(process.env.VUE_APP_API_URL+'users'),
            loginError: null
        };
    },
    beforeMount() {
        onMounted(() => {
            UserService.loadUserList().then((result) => {
                this.rowData = result;
                console.log(this.rowData);
            });
        });

    }
});
</script>