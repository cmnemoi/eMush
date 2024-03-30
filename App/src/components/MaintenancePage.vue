<template>
    <div class="box-container">
        <h1 class="title">{{ $t("errors.title") }}</h1>
        <div class="error-container">
            <img class="neron-img" :src="getAssetUrl('neron_eye.gif')" alt="Neron">
            <span class="neron-message" v-html="$t('errors.neronMessage')"></span>
            <p class="error">{{ $t('errors.gameInMaintenance') }}</p>
            <p class="community" v-html="$t('errors.consultCommunity')"></p>
        </div>
    </div>
</template>

<script lang="ts">
import { getAssetUrl } from "@/utils/getAssetUrl";
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'MaintenancePage',
    props: {
        error: {
            type: Object,
            default: null
        }
    },
    methods: {
        getAssetUrl
    }
});

</script>

<style scoped lang="scss">
.box-container {
    &::v-deep(strong) {
        color: $cyan;
    }

    &::v-deep(a) {
        color: $green;
    }

    .title {
        font-size: 17pt;
        margin-bottom: 15px;
    }

    .error-container {
        display: block;
        margin: 5px 0 10px 0;
        font-size: 11pt;

        .neron-img {
            float: left;
            width: 100px;
            height: 100px;
            margin-right: 10px;
        }

        .neron-message {
            display: inline;
        }

        .error {
            margin: 10px 10px;
            margin-left: 110px;
            padding: 5px 10px;
            border: 1px solid $red;
            background-color: #222b6b;
        }
    }
}
</style>
