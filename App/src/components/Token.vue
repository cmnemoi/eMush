<template>
    <div>
        <p v-if="errorMessage">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script>
import { mapActions } from "vuex";
import router from "@/router";

export default {
    name: "Token",
    data: () => {
        return {
            errorMessage: ''
        };
    },
    async beforeMount() {
        if (typeof this.$route.query.code !== 'undefined') {
            const logginSuccess = await this.login({ code: this.$route.query.code });
            if (logginSuccess) {
                router.push({ name: 'GamePage' });
            }
        }

        if (typeof this.$route.query.error !== 'undefined') {
            this.errorMessage = this.$route.query.error;
        }
    },
    methods: {
        ...mapActions('auth', [
            'login'
        ])
    }
};
</script>
