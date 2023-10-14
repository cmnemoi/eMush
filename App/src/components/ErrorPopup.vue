<template>
    <PopUp :is-open="isError()" @close="clearError">
        <h1 class="title">{{ $t(['errors.status', errorStatus].join('.')) }}</h1>
        <span v-if="isWorkingServerError()">{{ $t('errors.reportToDevs') }}</span>
        <span v-else>{{ $t('errors.problem') }}</span>
        <div class="details">
            <span v-if="error.request.method">method: {{ error.request.method.toUpperCase() }}</span>
            <span v-if="error.request.url">url: {{ error.request.url }}</span>
            <span v-if="error.request.params">params: {{ error.request.params }}</span>
            <span v-if="error.response.details">details: {{ $t(['errors', error.response.details].join('.')) }}</span>
            <span v-if="error.response.class">class: {{ error.response.class }}</span>
            <span><br></span>
            <span v-html="$t('errors.consultCommunity')"></span>
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
        errorStatus(): string {
            return (! this.error.status || ! this.error.statusText)
                ? this.error.message
                : this.error.status;
        }
    },
    methods: {
        ...mapActions('error', [
            'clearError'
        ]),
        isError() {
            return this.error !== null;
        },
        isWorkingServerError() {
            const isServerError = this.isError() && (parseInt(this.error.status) >= 500 && parseInt(this.error.status) < 600);
            const isNot503Error = this.isError() && parseInt(this.error.status) !== 503;
            return isServerError && isNot503Error;
        }
    }
});
</script>

<style lang="scss" scoped>

::v-deep a {
    color: $green;
}

p { font-size: 1.2em; }

.details {
    padding-left: 0.6em;
    border-left: 2px solid $green;
    color: $green;
    font-style: italic;
}

</style>
