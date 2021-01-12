<template>
    <PopUp :is-open="!! error" @close="clearError">
        <h1 class="error-message">
            {{ error.message }}
        </h1>
        Please report this error to the devs, with details on how it happened.
    </PopUp>
</template>

<script>
import { mapState, mapActions } from "vuex";
import PopUp from "@/components/Utils/PopUp";

export default {
    components: {
        PopUp
    },
    computed: {
        ...mapState('error', [
            'error'
        ])
    },
    methods: {
        ...mapActions('error', [
            'clearError'
        ])
    }
};
</script>

<style lang="scss" scoped>

.error-message {
    margin-top: 0;
}

</style>
