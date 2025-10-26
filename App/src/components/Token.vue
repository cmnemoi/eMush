<template>
    <div>
        <p v-if="errorMessage">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script lang="ts">
import router from "@/router";
import { TokenService } from "@/services/storage.service";
import { defineComponent } from "vue";
import { mapActions } from "vuex";

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

        // Validate the OAuth state parameter to prevent CSRF attacks
        const state = this.$route.query.state?.toString();
        if (!state || !TokenService.validateOAuthState(state)) {
            this.errorMessage = 'Invalid OAuth state parameter.';
            console.error(this.errorMessage);
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
