<template>
    <div>
        <p v-if="errorMessage">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script lang="ts">
import { mapActions } from "vuex";
import router from "@/router";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Token",
    data: () => {
        return {
            errorMessage: ''
        };
    },
    async beforeMount(): Promise<void> {
        if (this.$route.query.code === undefined) {
            return;
        }

        if (this.$route.query.error) {
            this.errorMessage = this.$route.query.error.toString();
            return;
        }

        const loginSuccessful = await this.login({ code: this.$route.query.code });
        if (!loginSuccessful) {
            return;
        }

        router.push({ name: 'GamePage' });
    },
    methods: {
        ...mapActions({
            login: 'auth/login'
        })
    }
});
</script>
