<template>
    <PopUp :is-open="isError() && isGamePage()" @close="clearErrorAndReloadData">
        <h1 class="title">{{ getTranslatedErrorStatus() }}</h1>
        <div class="message">
            <img class="neron-img" src="@/assets/images/neron_eye.gif" alt="Neron">
            <div>
                <p v-if="isWorkingServerError()">{{ $t('errors.reportToDevs') }}</p>
                <p v-else>{{ $t('errors.problem') }}</p>
                <div class="code">
                    <span v-if="error.request.method">{{ $t('errors.details.method') }} {{ error.request.method.toUpperCase() }}</span>
                    <span v-if="error.request.url">{{ $t('errors.details.url') }} {{ error.request.url }}</span>
                    <span class="details" v-if="error.request.params">{{ $t('errors.details.params') }} <strong>{{ error.request.params }}</strong></span>
                    <span class="details" v-if="getTranslatedErrorDetails()">{{ $t('errors.details.message') }} <strong>{{ getTranslatedErrorDetails() }}</strong></span>
                    <span class="details" v-if="error.response.class">{{ $t('errors.details.class') }} <strong>{{ error.response.class }}</strong></span>
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
        ...mapActions({
            clearError: 'error/clearError',
            loadChannels: 'communication/loadChannels',
            loadRoomLogs: 'communication/loadRoomLogs',
            reloadPlayer: 'player/reloadPlayer'
        }),
        clearErrorAndReloadData() {
            this.clearError();
            this.reloadPlayer();
            this.loadChannels();
            this.loadRoomLogs();
        },
        getTranslatedErrorDetails(): string | null {
            // If the error is a 502, it's probably due to server synchronization
            // but we don't have much details about it. Then, hardcode the error message.
            if (parseInt(this.errorStatus) === 502) {
                return this.$t('errors.badGateway');
            }

            // If there is no details, return null.
            if (!this.error.response.details) {
                return null;
            }

            // Else, try to translate the error message. If there is no translation key associated, 
            // return the raw error message.
            const translatedDetails = this.$t(['errors', this.error.response.details].join('.'));
            if (translatedDetails === ['errors', this.error.response.details].join('.')) {
                return this.error.response.details;
            } else {
                return translatedDetails;
            }
        },
        getTranslatedErrorStatus(): string {
            const translatedStatus = this.$t(['errors.status', this.errorStatus].join('.'));
            if (translatedStatus === ['errors.status', this.errorStatus].join('.')) {
                return this.errorStatus;
            }

            return translatedStatus;
        },
        isError() {
            return this.error !== null && parseInt(this.error.status) >= 400;
        },
        isGamePage() {
            return this.$route.name === 'GamePage';
        },
        isWorkingServerError() {
            const errorStatus = this.isError() ? parseInt(this.error.status) : null;
            const isServerError = errorStatus && errorStatus >= 500 && errorStatus < 600;
            const isNot503Or502Error = errorStatus !== 503 && errorStatus !== 502;
            return isServerError && isNot503Or502Error;
        }
    }
});
</script>

<style lang="scss" scoped>

::v-deep(a) {
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
