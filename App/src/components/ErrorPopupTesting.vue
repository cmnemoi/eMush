<template>
    <Toast :is-open="false" title="This is an error !" type="error">
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
    <Toast :is-open="false" title="This is a warning !" type="warning">
        <div class="message">
            <div>
                <p>{{ $t('errors.reportToDevs') }}</p>
                <div class="code">
                    <span>url: http://localhost:8080/api/v1/news</span>
                </div>
            </div>
        </div>
    </Toast>
    <Toast :is-open="false" title="This is an information !" type="info">
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

::v-deep(a) {
    color: $green;
}

.title {
    font-size: 1em;
    margin-bottom: 0.3em;
}

p {
    font-size: 1.2em;
    margin: 0.3em 0;
}

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
