<template>
    <PopUp :is-open="isWorkingServerError()" @close="clearError">
        <h1 class="title">
            {{ title }}
        </h1>
        Please report this error to the devs, with details on how it happened.
        <div class="details">
            <span v-if="error.request.method">method: {{ error.request.method.toUpperCase() }}</span>
            <span v-if="error.request.url">url: {{ error.request.url }}</span>
            <span v-if="error.request.params">params: {{ error.request.params }}</span>
            <span v-if="error.response.details">details: {{ error.response.details }}</span>
            <span v-if="error.response.class">class: {{ error.response.class }}</span>
        </div>
    </PopUp>
</template>

<script lang="ts">
import { mapState, mapActions } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        PopUp
    },
    computed: {
        ...mapState('error', [
            'error'
        ]),
        title(): string {
            console.error(this.error);
            return (! this.error.status || ! this.error.statusText)
                ? this.error.message
                : `${this.error.status} ${this.error.statusText}`;
        }
    },
    methods: {
        ...mapActions('error', [
            'clearError'
        ]),
        isWorkingServerError() {
            return this.error && (parseInt(this.error.status) >= 500 && parseInt(this.error.status) <= 599) && this.error.status != 503;
        }
    }
});
</script>

<style lang="scss" scoped>

.title {
    margin-top: 0;
}

.details {
    margin-top: 16px;
    font-size: .65em;
}

</style>
