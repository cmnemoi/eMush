<template>
    <div class="box-container">
        <h1>{{ title || $t("errors.title") }}</h1>
        <div class="flex-row">
            <img class="neron-img" :src="getImgUrl('neron_eye.gif')" alt="Neron">
            <div class="neron-message">
                <span v-html="$t('errors.neronMessage')"></span>
                <p class="error">{{ errorMessage }}</p>
            </div>
        </div>
        <p class="community" v-html="$t('errors.consultCommunity')"></p>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent({
    name: "ErrorPage",
    props: {
        errorMessage: {
            type: String,
            required: true
        },
        title: {
            type: String,
            default: ""
        }
    },
    methods: {
        getImgUrl
    }
});
</script>

<style scoped lang="scss">
:deep(strong) {
    color: $cyan;
}

:deep(a) {
    color: $green;
}

.neron-img {
    float: left;
    width: 100px;
    height: 100px;
}

.neron-message {
    flex-direction: column;
    font-size: 11pt;

    .error {
        padding: 5px 10px;
        border: 1px solid $red;
        background-color: #222b6b;
    }
}
</style>


