<template>
    <div class="toast-container">
        <Toast :is-open="true" title="This is an error !" type="error">
            <div class="message">
                <div>
                    <p>{{ $t('errors.reportToDevs') }}</p>
                    <div class="code">
                        <span>method: GET</span>
                        <span>url: http://localhost:8080/api/v1/news</span>
                    </div>
                    <p v-html="$t('errors.consultCommunity')"></p>
                </div>
            </div>
        </Toast>
        <Toast :is-open="true" title="This is a warning !" type="warning">
            <div class="message">
                <div>
                    <p>{{ $t('errors.reportToDevs') }}</p>
                    <div class="code">
                        <span>url: http://localhost:8080/api/v1/news</span>
                    </div>
                </div>
            </div>
        </Toast>
        <Toast :is-open="true" title="This is an information !" type="info">
            <div class="message">
                <div>
                    <p>Hey, how are you ?</p>
                </div>
            </div>
        </Toast>
        <Toast :is-open="true" title="This is a success !" type="success">
            <div class="message">
                <div>
                    <p>Hey, how are you ?</p>
                </div>
            </div>
        </Toast>
    </div>
    <div style="display: none;" class="toast-background"></div>
</template>

<script lang="ts">
import { mapState, mapActions } from "vuex";
import Toast from "@/components/Utils/Toast.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        Toast
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
    }
});
</script>

<style lang="scss" scoped>

.toast-container {
    position: fixed;
    z-index: 999;
    bottom: 1em;
    width: 100%;
    padding: 0 0.8em;
    flex-direction: column-reverse;
    align-items: center;
    row-gap: 1.2em;

    @media screen and (max-width: $breakpoint-desktop-m) {
        font-size: 0.9em;
        row-gap: 0.8em;
    }
}

.code {
    padding: 0.4em 0.8em;
    margin: 0.4em -0.1em;
    border: 1px solid $greyBlue;
    border-radius: 4px;
    background-color: rgba(35, 37, 100, 1);
    box-shadow: inset 0 0 4px $deepBlue;
    // font-family: 'Courier New', Courier, monospace;
    line-height: 1.4em;

    .details { margin-top: 0.4em; }

    strong { letter-spacing: 0.03em; }
}

.toast-background {
    position: fixed;
    background: transparentize(#09092d, 0.5);
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 998;
    transition: all 0.3s;
}

</style>
