<template>
    <PopUp :is-open="isError()" @close="clearError">
        <h1 class="title">{{ $t(['errors.status', errorStatus].join('.')) }}</h1>
        <div class="message">
            <img class="neron-img" src="@/assets/images/neron_eye.gif" alt="Neron">
            <div>
                <p v-if="isWorkingServerError()">{{ $t('errors.reportToDevs') }}</p>
                <p v-else>{{ $t('errors.problem') }}</p>
                <div class="code">
                    <span v-if="error.request.method">method: {{ error.request.method.toUpperCase() }}</span>
                    <span v-if="error.request.url">url: {{ error.request.url }}</span>
                    <span class="details" v-if="error.request.params">params: <strong>{{ error.request.params }}</strong></span>
                    <span class="details" v-if="error.response.details">details: <strong>{{ $t(['errors', error.response.details].join('.')) }}</strong></span>
                    <span class="details" v-if="error.response.class">class: <strong>{{ error.response.class }}</strong></span>
                </div>
                <p v-html="$t('errors.consultCommunity')"></p>
            </div>
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

.title {
    font-size: 1.5em;
    margin-bottom: 0.5em;
}

p { font-size: 1.2em; }

.message {
    flex-direction: row;
    align-items: flex-start;
    gap: 0.8em;

    img { margin: 1em 0; }
}

.code {
    padding: 0.8em 1.4em;
    border: 1px solid $red;
    border-radius: 4px;
    background-color: #222b6b;
    line-height: 1.4em;

    .details { margin-top: 0.4em; }

    strong { letter-spacing: 0.03em; }
}

</style>
