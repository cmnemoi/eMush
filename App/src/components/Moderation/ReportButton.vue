<template>
    <Tippy tag="span" class="report-button">
        <img :src="getImgUrl('comms/alert.png')" alt="Report message">
        <template #content>
            <h1>{{ $t('moderation.report.name') }}</h1>
            <p>{{ $t('moderation.report.description') }}</p>
        </template>
    </Tippy>
</template>

<script setup lang="ts">
import { Tippy } from "vue-tippy";
import { getImgUrl } from "@/utils/getImgUrl";
</script>

<style lang="scss" scoped>
.report-button {
    cursor: pointer;
}
</style>
