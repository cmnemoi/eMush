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
        if (typeof this.$route.query.code !== 'undefined') {
            const logginSuccess = await this.login({ code: this.$route.query.code });
            if (logginSuccess) {
                router.push({ name: 'GamePage' });
            }
        }

        if (typeof this.$route.query.error !== 'undefined' && this.$route.query.error !== null) {
            this.errorMessage = this.$route.query.error.toString();
        }
    },
    methods: {
        ...mapActions('auth', [
            'login'
        ])
    }
});
</script>
